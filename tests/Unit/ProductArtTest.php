<?php

namespace Tests\Unit;

use App\Support\ProductArt;
use PHPUnit\Framework\TestCase;

class ProductArtTest extends TestCase
{
    public function test_there_are_thirty_illustrations(): void
    {
        $this->assertCount(30, ProductArt::names());
    }

    public function test_every_illustration_is_a_well_formed_svg_with_the_logo(): void
    {
        foreach (ProductArt::names() as $name) {
            $svg = ProductArt::svg($name);

            $this->assertNotNull($svg, $name);
            $this->assertStringStartsWith('<svg class="product-art"', $svg, $name);
            $this->assertStringContainsString('<use href="#fleur"', $svg, "{$name} : logo absent");

            $doc = new \DOMDocument;
            $this->assertTrue($doc->loadXML($svg), "{$name} : SVG mal formé");
        }
    }

    public function test_the_striped_long_sleeve_tee_has_stripes_in_its_colour(): void
    {
        $svg = ProductArt::svg('T-shirt Manches Longues Rayé');

        $this->assertGreaterThanOrEqual(10, substr_count($svg, 'fill="#226192"'));
        $this->assertStringContainsString('Illustration : T-shirt Manches Longues Rayé', $svg);
    }

    public function test_unknown_product_has_no_illustration(): void
    {
        $this->assertFalse(ProductArt::has('Produit inconnu'));
        $this->assertNull(ProductArt::svg('Produit inconnu'));
        $this->assertFalse(ProductArt::has(null));
    }

    public function test_product_name_is_escaped_in_the_label(): void
    {
        // Le nom ne sert que de clé : un nom piégé ne produit aucune illustration, donc aucun HTML.
        $this->assertNull(ProductArt::svg('<script>alert(1)</script>'));
    }
}
