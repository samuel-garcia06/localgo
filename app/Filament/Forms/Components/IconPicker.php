<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class IconPicker extends Field
{
    protected string $view = 'filament.forms.components.icon-picker';

    public static function icons(): array
    {
        return [
            ['value' => 'pizza',     'emoji' => '🍕', 'label' => 'Pizza'],
            ['value' => 'burger',    'emoji' => '🍔', 'label' => 'Hamburguesa'],
            ['value' => 'chicken',   'emoji' => '🍗', 'label' => 'Pollo'],
            ['value' => 'kebab',     'emoji' => '🥙', 'label' => 'Kebab'],
            ['value' => 'fries',     'emoji' => '🍟', 'label' => 'Fritas'],
            ['value' => 'wings',     'emoji' => '🍖', 'label' => 'Carne'],
            ['value' => 'salad',     'emoji' => '🥗', 'label' => 'Ensalada'],
            ['value' => 'drink',     'emoji' => '🥤', 'label' => 'Bebida'],
            ['value' => 'sauce',     'emoji' => '🥣', 'label' => 'Salsas'],
            ['value' => 'combo',     'emoji' => '🍽️', 'label' => 'Menú'],
            ['value' => 'rice',      'emoji' => '🍚', 'label' => 'Arroz'],
            ['value' => 'pasta',     'emoji' => '🍝', 'label' => 'Pasta'],
            ['value' => 'sandwich',  'emoji' => '🥪', 'label' => 'Bocadillo'],
            ['value' => 'taco',      'emoji' => '🌮', 'label' => 'Taco'],
            ['value' => 'hotdog',    'emoji' => '🌭', 'label' => 'Hot Dog'],
            ['value' => 'fish',      'emoji' => '🐟', 'label' => 'Pescado'],
            ['value' => 'shrimp',    'emoji' => '🍤', 'label' => 'Marisco'],
            ['value' => 'sushi',     'emoji' => '🍣', 'label' => 'Sushi'],
            ['value' => 'soup',      'emoji' => '🍲', 'label' => 'Sopa/Guiso'],
            ['value' => 'dessert',   'emoji' => '🍰', 'label' => 'Postre'],
            ['value' => 'ice-cream', 'emoji' => '🍦', 'label' => 'Helado'],
            ['value' => 'coffee',    'emoji' => '☕',  'label' => 'Café'],
            ['value' => 'bakery',    'emoji' => '🥐', 'label' => 'Panadería'],
            ['value' => 'bread',     'emoji' => '🍞', 'label' => 'Pan'],
            ['value' => 'snack',     'emoji' => '🍿', 'label' => 'Snack / Nachos'],
            ['value' => 'chips',     'emoji' => '🥨', 'label' => 'Chips / Aperitivo'],
            ['value' => 'offer',     'emoji' => '🏷️', 'label' => 'Oferta'],
            ['value' => 'utensils',  'emoji' => '🍴', 'label' => 'General'],
        ];
    }
}
