<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\ProductVariation;
use App\Models\ProductVariationAttribute;
use Illuminate\Database\Seeder;

class ProductVariationAttributeSeeder extends Seeder
{
    public function run(): void
    {
        ProductVariationAttribute::query()->delete();

        foreach (ProductVariation::all() as $variation) {

            switch ($variation->sku) {

                /**
                 * Royal Canin
                 */
                case 'RC-MINI-1KG':
                    $this->attach($variation, 'weight', '1 کیلوگرم');
                    $this->attach($variation, 'age', 'بالغ');
                    break;

                case 'RC-MINI-2KG':
                    $this->attach($variation, 'weight', '2 کیلوگرم');
                    $this->attach($variation, 'age', 'بالغ');
                    break;

                /**
                 * Reflex Cat
                 */
                case 'REF-CAT-1KG':
                    $this->attach($variation, 'weight', '1 کیلوگرم');
                    $this->attach($variation, 'age', 'بالغ');
                    break;

                case 'REF-CAT-2KG':
                    $this->attach($variation, 'weight', '2 کیلوگرم');
                    $this->attach($variation, 'age', 'بالغ');
                    break;

                /**
                 * Treat
                 */
                case 'DOG-TREAT-CHICKEN':
                    $this->attach($variation, 'flavor', 'مرغ');
                    break;

                /**
                 * Collar
                 */
                case 'COLLAR-M':
                    $this->attach($variation, 'size', 'M');
                    $this->attach($variation, 'material', 'چرم');
                    break;

                case 'COLLAR-L':
                    $this->attach($variation, 'size', 'L');
                    $this->attach($variation, 'material', 'چرم');
                    break;

                /**
                 * Ball
                 */
                case 'BALL-RED':
                    $this->attach($variation, 'color', 'قرمز');
                    break;

                case 'BALL-BLUE':
                    $this->attach($variation, 'color', 'آبی');
                    break;

                /**
                 * Cat litter
                 */
                case 'CAT-LITTER-10':
                    $this->attach($variation, 'weight', '10 کیلوگرم');
                    break;

                case 'CAT-LITTER-20':
                    $this->attach($variation, 'weight', '20 کیلوگرم');
                    break;
            }
        }
    }

    private function attach(ProductVariation $variation, string $attributeSlug, string $optionLabel): void
    {
        $attribute = Attribute::query()
            ->where('slug', $attributeSlug)
            ->first();

        if (! $attribute) {
            return;
        }

        $option = AttributeOption::query()
            ->where('attribute_id', $attribute->id)
            ->where('label', $optionLabel)
            ->first();

        if (! $option) {
            return;
        }

        ProductVariationAttribute::query()->updateOrCreate(
            [
                'product_variation_id' => $variation->id,
                'attribute_id' => $attribute->id,
            ],
            [
                'attribute_option_id' => $option->id,
            ]
        );
    }
}
