<?php

namespace App\Filament\Pages;

use App\Enums\RestaurantOperationalStatus;
use App\Models\RestaurantSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RestaurantSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $title = 'Configuración del restaurante';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.restaurant-settings';

    public ?array $data = [];

    /** Gestionado fuera del form schema para control total sobre el picker de sonido. */
    public string $notificationSound = 'classic';

    public function mount(): void
    {
        $s = RestaurantSetting::current();

        $this->notificationSound = $s->notification_sound ?? 'classic';

        $this->form->fill([
            'operational_status' => $s->operational_status->value,
            'default_preparation_time' => $s->default_preparation_time,
            'allow_delivery' => $s->allow_delivery,
            'allow_pickup' => $s->allow_pickup,
            'minimum_order' => number_format((float) $s->minimum_order, 2, ',', ''),
            'delivery_fee' => number_format((float) $s->delivery_fee, 2, ',', ''),
            'customer_notice' => $s->customer_notice,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

                // ── Sección 1: Estado operativo ─────────────────────────────
                Section::make('Estado operativo')
                    ->description('Controla si el restaurante acepta pedidos y cuánto tardan en prepararse.')
                    ->icon('heroicon-o-bolt')
                    ->schema([
                        Select::make('operational_status')
                            ->label('Estado del restaurante')
                            ->options(collect(RestaurantOperationalStatus::cases())->mapWithKeys(
                                fn ($case) => [$case->value => $case->emoji().' '.$case->label()]
                            ))
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),

                        ToggleButtons::make('default_preparation_time')
                            ->label('Tiempo de preparación por defecto')
                            ->helperText('Se asigna automáticamente a cada pedido al aceptarlo. El restaurante puede ajustarlo manualmente antes de confirmar.')
                            ->options([
                                15 => '15 min',
                                20 => '20 min',
                                25 => '25 min',
                                30 => '30 min',
                                35 => '35 min',
                                40 => '40 min',
                                45 => '45 min',
                                50 => '50 min',
                                60 => '60 min',
                            ])
                            ->default(20)
                            ->required()
                            ->inline()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // ── Sección 2: Entrega y recogida ───────────────────────────
                Section::make('Entrega y recogida')
                    ->description('Define qué modalidades están disponibles y los costes asociados.')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Toggle::make('allow_delivery')
                            ->label('Permitir entrega a domicilio')
                            ->helperText('Si se desactiva, los clientes solo podrán elegir recogida en local.')
                            ->inline(false),

                        Toggle::make('allow_pickup')
                            ->label('Permitir recogida en local')
                            ->helperText('Si se desactiva, los clientes solo podrán elegir entrega a domicilio.')
                            ->inline(false),

                        TextInput::make('minimum_order')
                            ->label('Pedido mínimo (€)')
                            ->helperText('Importe mínimo para confirmar un pedido de entrega a domicilio. 0 para sin mínimo.')
                            ->prefix('€')
                            ->placeholder('0,00')
                            ->inputMode('decimal')
                            ->rules(['nullable', 'regex:/^\d+([.,]\d{1,2})?$/'])
                            ->validationMessages(['regex' => 'Introduce un importe válido, por ejemplo 0 o 2,99.'])
                            ->default('0,00'),

                        TextInput::make('delivery_fee')
                            ->label('Coste de envío (€)')
                            ->helperText('Coste aplicado a pedidos de entrega a domicilio.')
                            ->prefix('€')
                            ->placeholder('0,00')
                            ->inputMode('decimal')
                            ->rules(['nullable', 'regex:/^\d+([.,]\d{1,2})?$/'])
                            ->validationMessages(['regex' => 'Introduce un importe válido, por ejemplo 2,99 o 3.50.'])
                            ->default('0,00'),
                    ])
                    ->columns(2),

                // ── Sección 3: Mensaje para clientes ────────────────────────
                Section::make('Mensaje para clientes')
                    ->description('Mensaje visible en la carta y el carrito. Úsalo para avisos temporales.')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        Textarea::make('customer_notice')
                            ->label('Mensaje de aviso')
                            ->helperText('Ejemplo: "Cerrado el lunes 7 por festivo." Déjalo vacío para no mostrar ningún aviso.')
                            ->rows(3)
                            ->maxLength(300)
                            ->placeholder('Escribe aquí el aviso que verán los clientes…'),
                    ]),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Acepta tanto coma (2,99) como punto (2.99) — normaliza a float para la DB
        foreach (['minimum_order', 'delivery_fee'] as $field) {
            $raw = str_replace(',', '.', (string) ($data[$field] ?? '0'));
            $data[$field] = max(0.0, (float) $raw);
        }

        $data['notification_sound'] = $this->notificationSound;

        RestaurantSetting::current()->update($data);

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }

    public function testSound(string $sound): void
    {
        $allowed = ['classic', 'bell', 'kitchen', 'short', 'intense'];
        $sound = in_array($sound, $allowed, true) ? $sound : 'classic';
        $this->dispatch('play-test-sound', sound: $sound);
    }
}
