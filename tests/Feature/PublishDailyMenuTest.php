<?php
namespace Tests\Feature;

use App\Domain\Publishing\PublishDailyMenu;
use App\Models\DailyMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishDailyMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_creates_immutable_snapshot_and_channel_states(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $menu = DailyMenu::create(['service_date' => today(), 'created_by' => $user->id, 'price_cents' => 1250]);
        $menu->items()->create(['category'=>'prato','name'=>'Prato teste','position'=>0,'available'=>true]);

        $publication = app(PublishDailyMenu::class)->execute($menu);
        $this->assertSame('Prato teste', $publication->payload['items'][0]['name']);
        $this->assertDatabaseHas('publication_attempts',['publication_id'=>$publication->id,'channel'=>'website','status'=>'published']);
        $this->assertDatabaseHas('publication_attempts',['publication_id'=>$publication->id,'channel'=>'facebook','status'=>'pending']);
    }
}
