<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $companyUser;
    private User $super;

    protected function setUp(): void
    {
        parent::setUp();

        $this->super = User::create([
            'name' => 'Super', 'email' => 'super@test.dev',
            'password' => bcrypt('password'), 'role' => 'super',
        ]);

        $this->company = Company::create(['name' => 'Test Agency']);
        $this->companyUser = User::create([
            'name' => 'Company', 'email' => 'company@test.dev',
            'password' => bcrypt('password'), 'role' => 'company', 'company_id' => $this->company->id,
        ]);
    }

    public function test_company_cannot_access_super_area(): void
    {
        $this->actingAs($this->companyUser)
            ->get('/super')
            ->assertForbidden();
    }

    public function test_super_can_access_approval_queue(): void
    {
        $this->actingAs($this->super)
            ->get('/super')
            ->assertOk()
            ->assertSee('Super Admin Console');
    }

    public function test_company_can_submit_content_for_approval(): void
    {
        $content = Content::create([
            'company_id' => $this->company->id,
            'user_id' => $this->companyUser->id,
            'title' => 'Konten Uji',
            'type' => 'single',
            'slides_count' => 1,
            'status' => 'draft',
            'design' => [],
            'files' => [],
        ]);

        $this->actingAs($this->companyUser)
            ->post("/tools/content/{$content->id}/submit")
            ->assertRedirect();

        $this->assertDatabaseHas('contents', ['id' => $content->id, 'status' => 'pending']);
    }

    public function test_super_can_approve_pending_content(): void
    {
        $content = Content::create([
            'company_id' => $this->company->id,
            'user_id' => $this->companyUser->id,
            'title' => 'Konten Uji',
            'type' => 'single',
            'slides_count' => 1,
            'status' => 'pending',
            'design' => [],
            'files' => [],
        ]);

        $this->actingAs($this->super)
            ->post("/super/contents/{$content->id}/approve", ['note' => 'Bagus!'])
            ->assertRedirect();

        $content->refresh();
        $this->assertSame('approved', $content->status);
        $this->assertSame($this->super->id, $content->approved_by);
        $this->assertNotNull($content->approved_at);
    }

    public function test_super_must_provide_note_to_reject(): void
    {
        $content = Content::create([
            'company_id' => $this->company->id,
            'user_id' => $this->companyUser->id,
            'title' => 'Konten Uji',
            'type' => 'single',
            'slides_count' => 1,
            'status' => 'pending',
            'design' => [],
            'files' => [],
        ]);

        $this->actingAs($this->super)
            ->post("/super/contents/{$content->id}/reject")
            ->assertSessionHasErrors('note');
    }
}