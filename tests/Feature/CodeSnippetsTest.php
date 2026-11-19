<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\CodeSnippet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodeSnippetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedContent();
    }

    private function snip(array $o = []): CodeSnippet
    {
        return CodeSnippet::create($o + ['name' => 'T', 'type' => 'html', 'code' => '<i id="x"></i>', 'position' => 'head', 'scope' => 'all', 'is_active' => true]);
    }

    private function admin(string $role = 'admin'): AdminUser
    {
        return AdminUser::create(['name' => 'A', 'email' => "$role@example.com", 'password' => 'correct-horse-battery', 'role' => $role]);
    }

    public function test_code_is_output_in_the_chosen_position_only(): void
    {
        $this->snip(['code' => '<meta name="s-head">', 'position' => 'head']);
        $this->snip(['code' => '<b id="s-start"></b>', 'position' => 'body_start']);
        $this->snip(['code' => '<b id="s-end"></b>', 'position' => 'body_end']);

        $html = $this->get('/')->assertOk()->getContent();
        $head = strpos($html, '<meta name="s-head">');
        $this->assertNotFalse($head);
        $this->assertLessThan(strpos($html, '</head>'), $head);
        $this->assertGreaterThan(strpos($html, '<body'), strpos($html, 'id="s-start"'));
        $this->assertLessThan(strpos($html, 'id="s-end"'), strpos($html, 'id="s-start"'));
        $this->assertGreaterThan(strpos($html, '</main>'), strpos($html, 'id="s-end"'));
        $this->assertLessThan(strpos($html, '</body>'), strpos($html, 'id="s-end"'));
    }

    public function test_bare_code_is_wrapped_and_tags_are_left_alone(): void
    {
        $this->snip(['type' => 'script', 'code' => 'window.__s1 = 1;']);
        $this->snip(['type' => 'style', 'code' => '.s2{color:red}']);
        $this->snip(['type' => 'script', 'code' => '<script src="https://example.com/s3.js"></script>']);

        $this->get('/')->assertSee("<script>\nwindow.__s1 = 1;\n</script>", false)
            ->assertSee("<style>\n.s2{color:red}\n</style>", false)
            ->assertSee('<script src="https://example.com/s3.js"></script>', false);
    }

    public function test_inactive_code_is_not_output(): void
    {
        $this->snip(['code' => '<meta name="off-one">', 'is_active' => false]);
        $this->get('/')->assertOk()->assertDontSee('off-one', false);
    }

    public function test_specific_pages_only_with_wildcards(): void
    {
        $this->snip(['code' => '<meta name="only-home">', 'scope' => 'specific', 'pages' => "/"]);
        $this->snip(['code' => '<meta name="only-tours">', 'scope' => 'specific', 'pages' => "https://example.com/tours/*\n/thank-you"]);

        $this->get('/')->assertSee('only-home', false)->assertDontSee('only-tours', false);
        $this->get('/tours/self-drive')->assertDontSee('only-home', false);
        $this->get('/about')->assertDontSee('only-home', false)->assertDontSee('only-tours', false);

        $this->get('/tours/self-drive')->assertSee('only-tours', false);
    }

    public function test_admin_can_manage_code_and_changes_show_straight_away(): void
    {
        $this->actingAs($this->admin(), 'admin')->get('/admin/snippets')->assertOk();
        $this->get('/admin/snippets/create')->assertOk();

        $this->post('/admin/snippets', ['name' => 'Pixel', 'type' => 'script', 'code' => 'window.__px=1;', 'position' => 'head', 'scope' => 'all', 'is_active' => '1'])
            ->assertRedirect('/admin/snippets');
        $this->get('/')->assertSee('window.__px=1;', false);

        $s = CodeSnippet::first();
        $this->put("/admin/snippets/{$s->id}", ['name' => 'Pixel', 'type' => 'script', 'code' => 'window.__px=2;', 'position' => 'body_end', 'scope' => 'all', 'is_active' => '0'])
            ->assertRedirect('/admin/snippets');
        $this->get('/')->assertDontSee('window.__px', false);

        $this->delete("/admin/snippets/{$s->id}")->assertRedirect('/admin/snippets');
        $this->assertDatabaseCount('code_snippets', 0);
    }

    public function test_specific_scope_requires_pages_and_values_are_validated(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/snippets', ['name' => 'X', 'type' => 'script', 'code' => 'a', 'position' => 'head', 'scope' => 'specific', 'pages' => ''])
            ->assertSessionHasErrors('pages');
        $this->post('/admin/snippets', ['name' => 'X', 'type' => 'script', 'code' => 'a', 'position' => 'nowhere', 'scope' => 'all'])
            ->assertSessionHasErrors('position');
    }

    public function test_editors_cannot_reach_header_and_footer_code(): void
    {
        $this->actingAs($this->admin('editor'), 'admin')->get('/admin/snippets')->assertForbidden();
    }
}
