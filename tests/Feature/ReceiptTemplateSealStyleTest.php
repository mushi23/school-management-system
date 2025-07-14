<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\ReceiptTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ReceiptTemplateSealStyleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_seal_style_field_exists_in_receipt_template()
    {
        // This test verifies that the seal_style field is properly defined in the model
        $template = new ReceiptTemplate();
        $fillable = $template->getFillable();
        
        $this->assertContains('seal_style', $fillable, 'seal_style should be in the fillable array');
    }

    public function test_seal_style_can_be_set_and_retrieved()
    {
        // Create a school and admin user
        $school = School::factory()->create();
        $user = User::factory()->create([
            'school_id' => $school->id,
        ]);

        // Create a receipt template
        $template = ReceiptTemplate::createDefaultForSchool($school->id);
        
        // Test setting and retrieving the seal_style
        $template->seal_style = 'modern';
        $template->save();
        
        $template->refresh();
        
        $this->assertEquals('modern', $template->seal_style);
    }

    public function test_seal_style_is_nullable()
    {
        // Create a school and admin user
        $school = School::factory()->create();
        $user = User::factory()->create([
            'school_id' => $school->id,
        ]);

        // Create a receipt template without seal_style
        $template = ReceiptTemplate::createDefaultForSchool($school->id);
        
        $this->assertNull($template->seal_style);
        
        // Set and then clear the seal_style
        $template->seal_style = 'classic';
        $template->save();
        
        $template->seal_style = null;
        $template->save();
        
        $template->refresh();
        $this->assertNull($template->seal_style);
    }
}
