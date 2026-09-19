<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Tickets;

use Fereydooni\LaravelTicketing\Actions\Replies\AddReplyAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Models\Attachment;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentsTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
        Storage::fake('local');
    }

    public function test_uploaded_files_are_stored_by_the_package_with_detected_metadata(): void
    {
        $requester = $this->user();

        $this->actingAs($requester)
            ->post('/api/ticketing/tickets', [
                'subject' => 'Screenshot attached',
                'files' => [UploadedFile::fake()->image('../../evil name.png')],
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        $attachment = Attachment::query()->sole();

        $this->assertSame('local', $attachment->disk);
        $this->assertStringStartsWith('ticketing/global/', $attachment->path);
        $this->assertSame('evil name.png', $attachment->original_name);
        $this->assertSame('image/png', $attachment->mime_type);
        $this->assertSame(64, strlen((string) $attachment->checksum));
        $this->assertSame((string) $requester->getKey(), (string) $attachment->uploaded_by_id);
        Storage::disk('local')->assertExists($attachment->path);
    }

    public function test_clients_cannot_register_arbitrary_storage_paths(): void
    {
        $requester = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $requester);

        $this->actingAs($requester)
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/replies', [
                'body' => 'See attached',
                'attachments' => [['disk' => 'local', 'path' => '.env', 'original_name' => 'env.txt']],
            ])
            ->assertCreated();

        $this->assertSame(0, Attachment::query()->count());
    }

    public function test_uploads_over_the_configured_limit_are_rejected(): void
    {
        config()->set('ticketing.attachments.max_upload_size_kb', 1);
        $requester = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $requester);

        $this->actingAs($requester)
            ->post('/api/ticketing/tickets/' . $ticket->getKey() . '/replies', [
                'body' => 'Big file',
                'files' => [UploadedFile::fake()->create('big.pdf', 50)],
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertSame(0, Attachment::query()->count());
    }

    public function test_participants_can_download_public_attachments_and_strangers_cannot(): void
    {
        $requester = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $requester);
        $entry = app(AddReplyAction::class)->add($ticket, [
            'body' => 'Log attached',
            'uploads' => [UploadedFile::fake()->createWithContent('log.txt', 'hello')],
        ], $requester);
        $attachment = $entry->attachments()->sole();

        $this->actingAs($requester)
            ->get($this->downloadUrl($ticket, $attachment))
            ->assertOk()
            ->assertDownload('log.txt');

        $this->actingAs($this->user())
            ->getJson($this->downloadUrl($ticket, $attachment))
            ->assertForbidden();
    }

    public function test_internal_note_attachments_are_hidden_from_requesters(): void
    {
        $requester = $this->user();
        $staff = $this->user(['ticket.note', 'ticket.view']);
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $requester);
        $note = app(AddReplyAction::class)->add($ticket, [
            'body' => 'Internal investigation',
            'entry_type' => 'internal_note',
            'uploads' => [UploadedFile::fake()->createWithContent('internal.txt', 'secret')],
        ], $staff);
        $attachment = $note->attachments()->sole();

        $this->assertSame('staff', $attachment->visibility_scope);

        $this->actingAs($requester)
            ->getJson($this->downloadUrl($ticket, $attachment))
            ->assertForbidden();

        $this->actingAs($requester)
            ->getJson('/api/ticketing/tickets/' . $ticket->getKey())
            ->assertOk()
            ->assertJsonCount(0, 'data.conversation');

        $this->actingAs($staff)
            ->getJson('/api/ticketing/tickets/' . $ticket->getKey())
            ->assertOk()
            ->assertJsonPath('data.conversation.0.body', 'Internal investigation')
            ->assertJsonPath('data.conversation.0.attachments.0.original_name', 'internal.txt')
            ->assertJsonPath('data.conversation.0.attachments.0.url', url($this->downloadUrl($ticket, $attachment)));

        $this->actingAs($staff)
            ->get($this->downloadUrl($ticket, $attachment))
            ->assertOk();
    }

    public function test_an_attachment_cannot_be_downloaded_through_another_ticket(): void
    {
        $requester = $this->user();
        $mine = app(CreateTicketAction::class)->create(['subject' => 'Mine'], $requester);
        $theirs = app(CreateTicketAction::class)->create([
            'subject' => 'Theirs',
            'uploads' => [UploadedFile::fake()->createWithContent('private.txt', 'private')],
        ], $this->user());

        $this->actingAs($requester)
            ->getJson($this->downloadUrl($mine, $theirs->attachments()->sole()))
            ->assertNotFound();
    }

    protected function downloadUrl(Ticket $ticket, Attachment $attachment): string
    {
        return '/api/ticketing/tickets/' . $ticket->getKey() . '/attachments/' . $attachment->getKey();
    }
}
