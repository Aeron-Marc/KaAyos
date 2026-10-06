<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Notifications\NewReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $worker;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client']);
        $this->worker = User::factory()->create(['role' => 'worker']);

        $this->booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->subDay(),
            'address'          => 'Address',
            'house_no'         => '123',
            'barangay'         => 'Brgy.',
            'status'           => Booking::STATUS_COMPLETED,
            'completed_at'     => now(),
        ]);
    }

    protected function createReview(array $overrides = []): Review
    {
        return Review::create(array_merge([
            'booking_id' => $this->booking->id,
            'client_id'  => $this->client->id,
            'worker_id'  => $this->worker->id,
            'rating'     => 3,
            'comment'    => 'Old comment about the work.',
        ], $overrides));
    }

    protected function backdateReview(int $hours): void
    {
        DB::table('reviews')
            ->where('booking_id', $this->booking->id)
            ->update([
                'created_at' => now()->subHours($hours),
                'updated_at' => now()->subHours($hours),
            ]);
    }

    protected function fakeJpeg(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'kaayosjpg');
        $jpeg = base64_decode(
            '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRof' .
            'Hh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwh' .
            'MjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAAR' .
            'CAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAA' .
            'gEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFh' .
            'cYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4i' .
            'JipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo' .
            '6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgEC' .
            'BAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8R' .
            'cYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiIm' .
            'KkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq' .
            '8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD3+iiigD//2Q=='
        );
        file_put_contents($path, $jpeg);

        return new UploadedFile($path, 'issue.jpg', 'image/jpeg', null, true);
    }

    public function test_client_can_edit_review_within_grace_period(): void
    {
        $this->createReview();

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $this->booking), [
                'rating'  => 5,
                'comment' => 'Updated comment — much better now.',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('review.rating', 5);

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $this->booking->id,
            'rating'     => 5,
            'comment'    => 'Updated comment — much better now.',
        ]);

        $this->assertEquals(5.0, (float) WorkerProfile::where('user_id', $this->worker->id)->value('average_rating'));
    }

    public function test_editing_after_grace_period_is_rejected(): void
    {
        $this->createReview();
        $this->backdateReview(73);

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $this->booking), [
                'rating'  => 1,
                'comment' => 'Too late to change this.',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The edit window for this review has expired.');

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $this->booking->id,
            'rating'     => 3,
        ]);
    }

    public function test_grace_period_is_configurable(): void
    {
        config(['kaayos.review_edit_grace_hours' => 0]);
        $this->createReview();

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $this->booking), [
                'rating'  => 5,
                'comment' => 'Should not be allowed.',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'The edit window for this review has expired.');
    }

    public function test_cannot_edit_someone_elses_review(): void
    {
        $this->createReview();
        $otherClient = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($otherClient)
            ->postJson(route('client.bookings.review', $this->booking), [
                'rating'  => 1,
                'comment' => 'Not my review.',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['booking_id' => $this->booking->id, 'rating' => 3]);
    }

    public function test_comment_only_edit_keeps_existing_photo(): void
    {
        Storage::fake('public');
        $this->createReview(['photo_path' => 'review-photos/existing.jpg']);
        Storage::disk('public')->put('review-photos/existing.jpg', 'binary');

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $this->booking), [
                'rating'  => 4,
                'comment' => 'Comment updated, photo untouched.',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $this->booking->id,
            'photo_path' => 'review-photos/existing.jpg',
        ]);
        Storage::disk('public')->assertExists('review-photos/existing.jpg');
    }

    public function test_new_photo_replaces_old_and_deletes_old_file(): void
    {
        Storage::fake('public');
        $this->createReview(['photo_path' => 'review-photos/old.jpg']);
        Storage::disk('public')->put('review-photos/old.jpg', 'binary');

        $response = $this->actingAs($this->client)->post(
            route('client.bookings.review', $this->booking),
            ['rating' => 4, 'comment' => 'New photo.', 'photo' => $this->fakeJpeg()],
            ['Accept' => 'application/json']
        );

        $response->assertOk()
            ->assertJsonPath('success', true);

        $review = Review::where('booking_id', $this->booking->id)->firstOrFail();
        $this->assertNotNull($review->photo_path);
        $this->assertNotEquals('review-photos/old.jpg', $review->photo_path);
        Storage::disk('public')->assertMissing('review-photos/old.jpg');
        Storage::disk('public')->assertExists($review->photo_path);
    }

    public function test_remove_photo_flag_clears_photo(): void
    {
        Storage::fake('public');
        $this->createReview(['photo_path' => 'review-photos/old.jpg']);
        Storage::disk('public')->put('review-photos/old.jpg', 'binary');

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $this->booking), [
                'rating'       => 3,
                'comment'      => 'Removed the photo.',
                'remove_photo' => 1,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $this->booking->id,
            'photo_path' => null,
        ]);
        Storage::disk('public')->assertMissing('review-photos/old.jpg');
    }

    public function test_review_notification_sent_only_on_first_submission(): void
    {
        Notification::fake();

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $this->booking), ['rating' => 5, 'comment' => 'First.']);

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $this->booking), ['rating' => 4, 'comment' => 'Edited.']);

        Notification::assertSentTimes(NewReview::class, 1);
    }

    public function test_reviews_page_shows_edit_controls_within_grace_period(): void
    {
        $this->createReview();

        $response = $this->actingAs($this->client)->get(route('client.reviews'));

        $response->assertOk();
        $response->assertSee('Editable until');
        $response->assertSee('Edit your review');
    }

    public function test_reviews_page_hides_edit_controls_after_grace_period(): void
    {
        $this->createReview();
        $this->backdateReview(73);

        $response = $this->actingAs($this->client)->get(route('client.reviews'));

        $response->assertOk();
        $response->assertDontSee('Editable until');
        $response->assertDontSee('Edit your review');
    }

    public function test_reviews_page_shows_edited_badge_after_update(): void
    {
        $this->createReview();

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $this->booking), ['rating' => 5, 'comment' => 'Updated.'])
            ->assertOk();

        $response = $this->actingAs($this->client)->get(route('client.reviews'));

        $response->assertOk();
        $response->assertSee('Edited');
    }
}
