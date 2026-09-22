<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $products = [

            // ─── HAMBURGUESAS ──────────────────────────────────────────────────────────
            [
                'category_slug' => 'hamburguesas',
                'name' => 'Urban Classic',
                'slug' => 'urban-classic',
                'description' => 'Medallón de ternera 100% Angus, queso cheddar fundido, lechuga iceberg, tomate maduro y nuestra salsa de la casa.',
                'price' => 9.90,
                'image_path' => 'products/catalog/burger-clasica.jpg',
            ],
            [
                'category_slug' => 'hamburguesas',
                'name' => 'Bacon BBQ',
                'slug' => 'bacon-bbq-burger',
                'description' => 'Ternera 100% Angus, bacon crujiente, cheddar ahumado, cebolla frita dorada y salsa barbacoa artesanal.',
                'price' => 11.90,
                'image_path' => 'products/catalog/burger-bbq.jpg',
            ],
            [
                'category_slug' => 'hamburguesas',
                'name' => 'Double Smash',
                'slug' => 'double-smash-burger',
                'description' => 'Doble smash de ternera Angus, doble cheddar americano fundido, pepinillos encurtidos y salsa especial Urban.',
                'price' => 13.90,
                'image_path' => 'products/catalog/burger-smash.jpg',
            ],
            [
                'category_slug' => 'hamburguesas',
                'name' => 'Crispy Chicken',
                'slug' => 'crispy-chicken-burger',
                'description' => 'Filete de pollo crujiente empanado en panko, col lombarda encurtida, tomate fresco y mayonesa chipotle.',
                'price' => 10.90,
                'image_path' => 'products/catalog/burger-chicken.jpg',
            ],
            [
                'category_slug' => 'hamburguesas',
                'name' => 'Tex-Mex',
                'slug' => 'tex-mex-burger',
                'description' => 'Ternera 100% Angus, jalapeños frescos, maíz tostado, queso cheddar y nuestra salsa guacamole.',
                'price' => 11.90,
                'image_path' => 'products/catalog/burger-texmex.jpg',
            ],
            [
                'category_slug' => 'hamburguesas',
                'name' => 'Veggie Deluxe',
                'slug' => 'veggie-deluxe-burger',
                'description' => 'Hamburguesa de legumbres y quinoa, aguacate machacado, rúcula fresca, tomate seco y pesto de albahaca.',
                'price' => 10.50,
                'image_path' => 'https://images.unsplash.com/photo-1525059696034-4967a8e1dca2?auto=format&fit=crop&w=1200&q=80',
            ],

            // ─── PIZZAS ────────────────────────────────────────────────────────────────
            [
                'category_slug' => 'pizzas',
                'name' => 'Margherita',
                'slug' => 'pizza-margherita',
                'description' => 'Tomate San Marzano D.O.P., mozzarella fior di latte y albahaca fresca. La pizza en su estado más puro.',
                'price' => 10.90,
                'image_path' => 'products/catalog/pizza-margarita.jpg',
            ],
            [
                'category_slug' => 'pizzas',
                'name' => 'Pepperoni',
                'slug' => 'pizza-pepperoni',
                'description' => 'Tomate artesanal, mozzarella extra y pepperoni americano de corte grueso horneado al punto.',
                'price' => 12.90,
                'image_path' => 'products/catalog/pizza-pepperoni.jpg',
            ],
            [
                'category_slug' => 'pizzas',
                'name' => 'Carbonara',
                'slug' => 'pizza-carbonara',
                'description' => 'Base de nata, panceta ahumada, champiñones laminados, mozzarella y parmesano rallado.',
                'price' => 13.50,
                'image_path' => 'products/catalog/pizza-carbonara.jpg',
            ],
            [
                'category_slug' => 'pizzas',
                'name' => 'BBQ Pollo',
                'slug' => 'pizza-bbq-pollo',
                'description' => 'Salsa barbacoa artesanal, pollo asado desmechado, cebolla morada, maíz dulce y mozzarella.',
                'price' => 13.90,
                'image_path' => 'products/catalog/pizza-bbq-pollo.jpg',
            ],
            [
                'category_slug' => 'pizzas',
                'name' => 'Prosciutto',
                'slug' => 'pizza-prosciutto',
                'description' => 'Mozzarella, jamón de Parma D.O.P., rúcula fresca, tomates cherry y virutas de parmesano.',
                'price' => 14.90,
                'image_path' => 'products/catalog/pizza-prosciutto.jpg',
            ],
            [
                'category_slug' => 'pizzas',
                'name' => 'Vegetal',
                'slug' => 'pizza-vegetal',
                'description' => 'Tomate, mozzarella, pimientos asados, calabacín a la plancha, champiñones y aceitunas negras.',
                'price' => 12.90,
                'image_path' => 'products/catalog/pizza-vegetal.jpg',
            ],

            // ─── KEBAB ─────────────────────────────────────────────────────────────────
            [
                'category_slug' => 'kebab',
                'name' => 'Döner Kebab',
                'slug' => 'doner-kebab',
                'description' => 'Pan de pita artesanal, carne de ternera y pollo marinada, lechuga, tomate, cebolla y salsa blanca.',
                'price' => 8.90,
                'image_path' => 'products/catalog/doner-kebab.jpg',
            ],
            [
                'category_slug' => 'kebab',
                'name' => 'Durum Kebab',
                'slug' => 'durum-kebab',
                'description' => 'Pan durum fino y crujiente, carne marinada con especias, verduras frescas y salsa de yogur con hierbas.',
                'price' => 9.50,
                'image_path' => 'products/catalog/durum-kebab.jpg',
            ],
            [
                'category_slug' => 'kebab',
                'name' => 'Shawarma de Pollo',
                'slug' => 'shawarma-pollo',
                'description' => 'Pollo marinado con especias árabes, pimientos asados, pepino encurtido y salsa de yogur con menta.',
                'price' => 9.90,
                'image_path' => 'products/catalog/chicken-shawarma.jpg',
            ],
            [
                'category_slug' => 'kebab',
                'name' => 'Kebab en Plato',
                'slug' => 'kebab-en-plato',
                'description' => 'Carne de kebab sobre arroz basmati, ensalada fresca y pepino, salsa blanca y salsa brava. Un plato completo.',
                'price' => 10.90,
                'image_path' => 'products/catalog/plato-kebab.jpg',
            ],
            [
                'category_slug' => 'kebab',
                'name' => 'Falafel Wrap',
                'slug' => 'falafel-wrap',
                'description' => 'Falafel casero de garbanzos, hummus cremoso, ensalada de pepino y tomate, y salsa tahini.',
                'price' => 9.50,
                'image_path' => 'products/catalog/falafel-wrap.jpg',
            ],

            // ─── CARNE & POLLO ─────────────────────────────────────────────────────────
            [
                'category_slug' => 'carne-pollo',
                'name' => 'Alitas BBQ',
                'slug' => 'alitas-bbq',
                'description' => 'Seis alitas de pollo glaseadas lentamente con salsa barbacoa artesanal. Crujientes por fuera, jugosas por dentro.',
                'price' => 8.90,
                'image_path' => 'products/catalog/alitas-bbq.jpg',
            ],
            [
                'category_slug' => 'carne-pollo',
                'name' => 'Nuggets x8',
                'slug' => 'nuggets-8',
                'description' => 'Ocho nuggets de pollo 100% pechuga con rebozado crujiente dorado. Acompañados de salsa a elegir.',
                'price' => 6.90,
                'image_path' => 'products/catalog/nuggets.jpg',
            ],
            [
                'category_slug' => 'carne-pollo',
                'name' => 'Tiras Crujientes',
                'slug' => 'tiras-crujientes',
                'description' => 'Cuatro tiras de pechuga de pollo empanadas en panko, extra crujientes y perfectas para mojar en salsa.',
                'price' => 8.50,
                'image_path' => 'products/catalog/tiras-crujientes.jpg',
            ],
            [
                'category_slug' => 'carne-pollo',
                'name' => 'Medio Pollo',
                'slug' => 'medio-pollo',
                'description' => 'Medio pollo asado lentamente, marinado con hierbas mediterráneas, piel dorada y jugoso por dentro.',
                'price' => 11.90,
                'image_path' => 'products/catalog/medio-pollo.jpg',
            ],
            [
                'category_slug' => 'carne-pollo',
                'name' => 'Popcorn Chicken',
                'slug' => 'popcorn-chicken',
                'description' => 'Bocaditos de pollo rebozados con especias, ultra crujientes y adictivos. Con salsa honey mustard.',
                'price' => 7.50,
                'image_path' => 'products/catalog/popcorn-chicken.jpg',
            ],

            // ─── ACOMPAÑANTES ──────────────────────────────────────────────────────────
            [
                'category_slug' => 'acompanantes',
                'name' => 'Patatas Fritas',
                'slug' => 'patatas-fritas',
                'description' => 'Patatas de corte clásico, fritas en aceite de girasol de alta oleico y terminadas con sal marina en escamas.',
                'price' => 3.50,
                'image_path' => 'products/catalog/patatas-fritas.jpg',
            ],
            [
                'category_slug' => 'acompanantes',
                'name' => 'Patatas Gajo',
                'slug' => 'patatas-gajo',
                'description' => 'Gajos de patata con piel especiados con paprika ahumada, ajo y romero. Crujientes por fuera, suaves por dentro.',
                'price' => 4.50,
                'image_path' => 'products/catalog/patatas-gajo.jpg',
            ],
            [
                'category_slug' => 'acompanantes',
                'name' => 'Patatas Bravas',
                'slug' => 'patatas-bravas',
                'description' => 'Patatas fritas con nuestra salsa brava de elaboración propia. Una de nuestras tapas favoritas.',
                'price' => 4.90,
                'image_path' => 'products/catalog/patatas-bravas.jpg',
            ],
            [
                'category_slug' => 'acompanantes',
                'name' => 'Loaded Fries',
                'slug' => 'loaded-fries',
                'description' => 'Patatas fritas cubiertas con salsa cheddar fundida, bacon crujiente y cebolla caramelizada.',
                'price' => 6.90,
                'image_path' => 'products/catalog/patatas-loaded.jpg',
            ],
            [
                'category_slug' => 'acompanantes',
                'name' => 'Aros de Cebolla',
                'slug' => 'aros-cebolla',
                'description' => 'Aros de cebolla dulce rebozados en tempura ligera y fritos hasta conseguir el crujiente perfecto.',
                'price' => 4.50,
                'image_path' => 'products/catalog/aros-cebolla.jpg',
            ],
            [
                'category_slug' => 'acompanantes',
                'name' => 'Pan de Ajo',
                'slug' => 'pan-de-ajo',
                'description' => 'Pan artesanal con mantequilla de ajo, perejil fresco y un toque de mozzarella gratinada.',
                'price' => 3.90,
                'image_path' => 'products/catalog/pan-de-ajo.jpg',
            ],

            // ─── ENSALADAS ─────────────────────────────────────────────────────────────
            [
                'category_slug' => 'ensaladas',
                'name' => 'César Clásica',
                'slug' => 'ensalada-cesar',
                'description' => 'Romana crujiente, pollo a la plancha, parmesano en lascas, crutones artesanales y aderezo César.',
                'price' => 9.90,
                'image_path' => 'products/catalog/ensalada-cesar.jpg',
            ],
            [
                'category_slug' => 'ensaladas',
                'name' => 'Griega',
                'slug' => 'ensalada-griega',
                'description' => 'Pepino, tomate cherry, cebolla morada, aceitunas Kalamata, queso feta griego y vinagreta de limón.',
                'price' => 9.50,
                'image_path' => 'products/catalog/ensalada-griega.jpg',
            ],
            [
                'category_slug' => 'ensaladas',
                'name' => 'De Atún',
                'slug' => 'ensalada-atun',
                'description' => 'Mezcla de lechugas, atún claro en aceite, maíz dulce, huevo duro, tomate y vinagreta suave de mostaza.',
                'price' => 9.90,
                'image_path' => 'products/catalog/ensalada-atun.jpg',
            ],

            // ─── BEBIDAS ───────────────────────────────────────────────────────────────
            [
                'category_slug' => 'bebidas',
                'name' => 'Coca-Cola',
                'slug' => 'coca-cola',
                'description' => 'El refresco de cola más icónico del mundo en lata de 33cl. Bien fría, siempre perfecta.',
                'price' => 2.50,
                'image_path' => 'products/catalog/coca-cola-lata.jpg',
            ],
            [
                'category_slug' => 'bebidas',
                'name' => 'Coca-Cola Zero',
                'slug' => 'coca-cola-zero',
                'description' => 'Todo el sabor de la Coca-Cola original sin azúcar ni calorías. Lata de 33cl.',
                'price' => 2.50,
                'image_path' => 'products/catalog/cocacola-zero.jpg',
            ],
            [
                'category_slug' => 'bebidas',
                'name' => 'Fanta Naranja',
                'slug' => 'fanta-naranja',
                'description' => 'Refresco de naranja con burbuja vibrante y sabor afrutado. El acompañante perfecto. 33cl.',
                'price' => 2.50,
                'image_path' => 'products/catalog/fanta-naranja.jpg',
            ],
            [
                'category_slug' => 'bebidas',
                'name' => 'Sprite',
                'slug' => 'sprite',
                'description' => 'Lima-limón refrescante con burbuja ligera y limpia. Perfecta para limpiar el paladar entre bocado y bocado. 33cl.',
                'price' => 2.50,
                'image_path' => 'products/catalog/sprite-lata.jpg',
            ],
            [
                'category_slug' => 'bebidas',
                'name' => 'Agua Mineral',
                'slug' => 'agua-mineral',
                'description' => 'Agua mineral natural sin gas, servida bien fría en botella de 50cl. La opción más limpia y ligera.',
                'price' => 1.90,
                'image_path' => 'products/catalog/agua-mineral.jpg',
            ],
            [
                'category_slug' => 'bebidas',
                'name' => 'Zumo de Naranja',
                'slug' => 'zumo-naranja',
                'description' => 'Zumo de naranja recién exprimido, sin azúcares añadidos y con toda la vitamina C de la fruta. 33cl.',
                'price' => 3.50,
                'image_path' => 'products/catalog/zumo-naranja.jpg',
            ],
            [
                'category_slug' => 'bebidas',
                'name' => 'Nestea Limón',
                'slug' => 'nestea-limon',
                'description' => 'Té frío de limón con el equilibrio perfecto entre dulce y refrescante. Para cuando el agua se queda corta. 33cl.',
                'price' => 2.50,
                'image_path' => 'products/catalog/nestea-limon.jpg',
            ],
            [
                'category_slug' => 'bebidas',
                'name' => 'Monster Energy',
                'slug' => 'monster-energy',
                'description' => 'Bebida energética Monster Original en lata de 50cl. Para cuando necesitas un extra de energía real.',
                'price' => 3.50,
                'image_path' => 'products/catalog/monster.jpg',
            ],
            [
                'category_slug' => 'bebidas',
                'name' => 'Red Bull',
                'slug' => 'red-bull',
                'description' => 'La bebida energética original en lata de 25cl. Te da alas en cualquier momento del día.',
                'price' => 3.20,
                'image_path' => 'products/catalog/red-bull.jpg',
            ],

            // ─── SALSAS ────────────────────────────────────────────────────────────────
            [
                'category_slug' => 'salsas',
                'name' => 'Alioli',
                'slug' => 'alioli',
                'description' => 'Alioli tradicional elaborado en cocina con ajo, huevo y aceite de oliva virgen extra. Receta propia.',
                'price' => 1.20,
                'image_path' => 'products/catalog/alioli.jpg',
            ],
            [
                'category_slug' => 'salsas',
                'name' => 'Salsa BBQ',
                'slug' => 'salsa-bbq',
                'description' => 'Salsa barbacoa artesanal con melaza de caña, chipotle ahumado y un toque sutil de miel.',
                'price' => 1.20,
                'image_path' => 'products/catalog/salsa-bbq.jpg',
            ],
            [
                'category_slug' => 'salsas',
                'name' => 'Miel Mostaza',
                'slug' => 'miel-mostaza',
                'description' => 'La combinación perfecta entre la dulzura natural de la miel y el carácter intenso de la mostaza de Dijon.',
                'price' => 1.20,
                'image_path' => 'products/catalog/miel-mostaza.jpg',
            ],
            [
                'category_slug' => 'salsas',
                'name' => 'Salsa Brava',
                'slug' => 'salsa-brava',
                'description' => 'Salsa brava de elaboración propia con pimentón picante, tomate y un toque de vinagre. Intensamente sabrosa.',
                'price' => 1.20,
                'image_path' => 'products/catalog/salsa-brava.jpg',
            ],
            [
                'category_slug' => 'salsas',
                'name' => 'Cheddar Fundido',
                'slug' => 'cheddar-fundido',
                'description' => 'Salsa de queso cheddar americano fundido, espesa y cremosa. Irresistible para mojar patatas o cubrir cualquier plato.',
                'price' => 1.50,
                'image_path' => 'products/catalog/salsa-cheddar.jpg',
            ],

            // ─── MENÚS ─────────────────────────────────────────────────────────────────
            [
                'category_slug' => 'menus',
                'name' => 'Menú Urban Burger',
                'slug' => 'menu-urban-burger',
                'description' => 'Urban Classic Burger + patatas fritas + bebida a elegir. El menú más solicitado de Urban Bites.',
                'price' => 14.90,
                'image_path' => 'products/catalog/menu-burger.jpg',
            ],
            [
                'category_slug' => 'menus',
                'name' => 'Menú Kebab',
                'slug' => 'menu-kebab',
                'description' => 'Döner Kebab o Durum + patatas fritas + bebida a elegir. El sabor mediterráneo completo.',
                'price' => 13.90,
                'image_path' => 'products/catalog/menu-kebab.jpg',
            ],
            [
                'category_slug' => 'menus',
                'name' => 'Menú Pizza Artesanal',
                'slug' => 'menu-pizza-artesanal',
                'description' => 'Pizza individual a elegir entre Margherita, Pepperoni o BBQ Pollo + bebida fría. Perfecto para uno.',
                'price' => 14.90,
                'image_path' => 'products/catalog/menu-pizza.jpg',
            ],

        ];

        foreach ($products as $data) {
            $categorySlug = $data['category_slug'];
            $category = $categories->get($categorySlug);

            Product::query()->create([
                'category_id' => $category->id,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'price' => $data['price'],
                'image_path' => $data['image_path'],
                'is_available' => true,
            ]);
        }
    }
}
