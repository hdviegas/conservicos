<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\AccountType;
use App\Enums\PixKeyType;
use App\Models\Position;
use App\Models\TransportVoucherType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->tabs([
                    Tab::make('Dados Pessoais')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nome Completo')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('social_name')
                                ->label('Nome Social')
                                ->maxLength(255),
                            TextInput::make('cpf')
                                ->label('CPF')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->mask('999.999.999-99')
                                ->maxLength(14),
                            TextInput::make('pis')
                                ->label('PIS/PASEP')
                                ->maxLength(15),
                            TextInput::make('rg')
                                ->label('RG')
                                ->maxLength(20),
                            DatePicker::make('birth_date')
                                ->label('Data de Nascimento')
                                ->displayFormat('d/m/Y'),
                            Select::make('gender')
                                ->label('Gênero')
                                ->options([
                                    'masculino' => 'Masculino',
                                    'feminino' => 'Feminino',
                                    'outros' => 'Outros',
                                ]),
                            Select::make('marital_status')
                                ->label('Estado Civil')
                                ->options([
                                    'solteiro' => 'Solteiro(a)',
                                    'casado' => 'Casado(a)',
                                    'divorciado' => 'Divorciado(a)',
                                    'uniao estavel' => 'União Estável',
                                    'separado' => 'Separado(a)',
                                    'viuvo' => 'Viúvo(a)',
                                ]),
                            TextInput::make('nationality')
                                ->label('Nacionalidade')
                                ->maxLength(50),
                            TextInput::make('naturality')
                                ->label('Naturalidade')
                                ->maxLength(100),
                            TextInput::make('father_name')
                                ->label('Nome do Pai')
                                ->maxLength(255),
                            TextInput::make('mother_name')
                                ->label('Nome da Mãe')
                                ->maxLength(255),
                            Select::make('blood_type')
                                ->label('Tipo Sanguíneo')
                                ->options([
                                    'A+' => 'A+',
                                    'A-' => 'A-',
                                    'B+' => 'B+',
                                    'B-' => 'B-',
                                    'AB+' => 'AB+',
                                    'AB-' => 'AB-',
                                    'O+' => 'O+',
                                    'O-' => 'O-',
                                ]),
                                TextInput::make('cnh')
                                ->label('CNH')
                                ->maxLength(20),
                            Select::make('cnh_category')
                                ->label('Categoria CNH')
                                ->options([
                                    'A' => 'A',
                                    'B' => 'B',
                                    'C' => 'C',
                                    'D' => 'D',
                                    'E' => 'E',
                                    'AB' => 'AB',
                                ]),
                            DatePicker::make('cnh_expiration')
                                ->label('Vencimento CNH')
                                ->displayFormat('d/m/Y'),
                        ])
                        ->columns(2),

                    Tab::make('Contato')
                        ->schema([
                            TextInput::make('phone')
                                ->label('Telefone')
                                ->tel()
                                ->maxLength(20),
                            TextInput::make('emergency_phone')
                                ->label('Telefone de Emergência')
                                ->tel()
                                ->maxLength(20),
                            TextInput::make('email')
                                ->label('E-mail')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('zip_code')
                                ->label('CEP')
                                ->maxLength(10),
                            TextInput::make('address')
                                ->label('Endereço')
                                ->maxLength(500)
                                ->columnSpanFull(),
                            TextInput::make('neighborhood')
                                ->label('Bairro')
                                ->maxLength(100),
                            TextInput::make('city')
                                ->label('Cidade')
                                ->maxLength(100),
                            Select::make('state')
                                ->label('UF')
                                ->options([
                                    'AC' => 'Acre',
                                    'AL' => 'Alagoas',
                                    'AP' => 'Amapá',
                                    'AM' => 'Amazonas',
                                    'BA' => 'Bahia',
                                    'CE' => 'Ceará',
                                    'DF' => 'Distrito Federal',
                                    'ES' => 'Espírito Santo',
                                    'GO' => 'Goiás',
                                    'MA' => 'Maranhão',
                                    'MT' => 'Mato Grosso',
                                    'MS' => 'Mato Grosso do Sul',
                                    'MG' => 'Minas Gerais',
                                    'PA' => 'Pará',
                                    'PB' => 'Paraíba',
                                    'PR' => 'Paraná',
                                    'PE' => 'Pernambuco',
                                    'PI' => 'Piauí',
                                    'RJ' => 'Rio de Janeiro',
                                    'RN' => 'Rio Grande do Norte',
                                    'RS' => 'Rio Grande do Sul',
                                    'RO' => 'Rondônia',
                                    'RR' => 'Roraima',
                                    'SC' => 'Santa Catarina',
                                    'SP' => 'São Paulo',
                                    'SE' => 'Sergipe',
                                    'TO' => 'Tocantins',
                                ]),
                        ])
                        ->columns(2),

                    Tab::make('Registro')
                        ->schema([
                            Select::make('company_id')
                                ->label('Empresa')
                                ->relationship('company', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Select::make('department_id')
                                ->label('Departamento')
                                ->relationship('department', 'name')
                                ->searchable()
                                ->preload(),
                            Select::make('position_id')
                                ->label('Cargo')
                                ->relationship('position', 'name')
                                ->searchable()
                                ->preload()
                                ->live(),
                            Select::make('work_schedule_id')
                                ->label('Escala de Trabalho')
                                ->relationship('workSchedule', 'name')
                                ->searchable()
                                ->preload(),
                            Select::make('transport_voucher_type_id')
                                ->label('Tipo de Vale Transporte')
                                ->options(
                                    TransportVoucherType::where('active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->placeholder('Sem Vale Transporte'),
                            TextInput::make('matricula')
                                ->label('Matrícula')
                                ->maxLength(20),
                            TextInput::make('folha')
                                ->label('Folha')
                                ->maxLength(20),
                            TextInput::make('ctps')
                                ->label('CTPS')
                                ->maxLength(30),
                            DatePicker::make('admission_date')
                                ->label('Data de Admissão')
                                ->displayFormat('d/m/Y'),
                            DatePicker::make('termination_date')
                                ->label('Data de Demissão')
                                ->displayFormat('d/m/Y'),
                            Textarea::make('termination_reason')
                                ->label('Motivo de Demissão')
                                ->columnSpanFull(),
                            DatePicker::make('hours_bank_start_date')
                                ->label('Início do Banco de Horas')
                                ->displayFormat('d/m/Y'),
                            Toggle::make('active')
                                ->label('Ativo')
                                ->default(true),
                            TextInput::make('external_id')
                                ->label('ID Externo (Relógio)')
                                ->maxLength(20),
                            Textarea::make('observations')
                                ->label('Observações')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Tab::make('Documentos')
                        ->schema([
                            Placeholder::make('documents_info')
                                ->label('')
                                ->content('Gerencie os documentos do funcionário na seção "Documentos" abaixo do formulário (disponível após salvar o registro).')
                                ->columnSpanFull(),
                        ])
                        ->columns(1),

                    Tab::make('Remuneração')
                        ->schema([
                            Placeholder::make('position_salary_info')
                                ->label('Salário Base do Cargo')
                                ->content(function (Get $get): string {
                                    $positionId = $get('position_id');
                                    if (! $positionId) {
                                        return 'Nenhum cargo selecionado';
                                    }
                                    $position = Position::find($positionId);
                                    if (! $position || ! $position->base_salary) {
                                        return 'Cargo sem salário base definido';
                                    }

                                    return 'R$ ' . number_format((float) $position->base_salary, 2, ',', '.');
                                }),
                            TextInput::make('salary_override')
                                ->label('Salário Individual')
                                ->helperText('Se informado, substitui o salário base do cargo para este funcionário')
                                ->numeric()
                                ->prefix('R$')
                                ->step('0.01')
                                ->minValue(0),
                            TextInput::make('gratificacao')
                                ->label('Gratificação')
                                ->helperText('Valor fixo de gratificação mensal')
                                ->numeric()
                                ->prefix('R$')
                                ->step('0.01')
                                ->minValue(0)
                                ->default(0),
                        ])
                        ->columns(2),

                    Tab::make('Compliance')
                        ->schema([
                            Placeholder::make('compliance_info')
                                ->label('')
                                ->content('Gerencie os exames ocupacionais e treinamentos nas abas de relacionamento abaixo (após salvar o registro).')
                                ->columnSpanFull(),
                        ])
                        ->columns(1),

                    Tab::make('Dados Bancários')
                        ->schema([
                            Select::make('bank_code')
                                ->label('Banco')
                                ->options([
                                    '001' => '001 - Banco do Brasil',
                                    '104' => '104 - Caixa Econômica Federal',
                                    '033' => '033 - Santander',
                                    '237' => '237 - Bradesco',
                                    '341' => '341 - Itaú',
                                    '260' => '260 - Nu Pagamentos (Nubank)',
                                    '077' => '077 - Banco Inter',
                                    '290' => '290 - PagBank',
                                    '323' => '323 - Mercado Pago',
                                    '336' => '336 - C6 Bank',
                                    '212' => '212 - Banco Original',
                                    '756' => '756 - Sicoob',
                                    '748' => '748 - Sicredi',
                                    '999' => 'Outro',
                                ])
                                ->live()
                                ->afterStateUpdated(function (?string $state, Set $set): void {
                                    $banks = [
                                        '001' => 'Banco do Brasil',
                                        '104' => 'Caixa Econômica Federal',
                                        '033' => 'Santander',
                                        '237' => 'Bradesco',
                                        '341' => 'Itaú',
                                        '260' => 'Nu Pagamentos (Nubank)',
                                        '077' => 'Banco Inter',
                                        '290' => 'PagBank',
                                        '323' => 'Mercado Pago',
                                        '336' => 'C6 Bank',
                                        '212' => 'Banco Original',
                                        '756' => 'Sicoob',
                                        '748' => 'Sicredi',
                                    ];

                                    if ($state && isset($banks[$state])) {
                                        $set('bank_name', $banks[$state]);
                                    }
                                }),
                            TextInput::make('bank_name')
                                ->label('Nome do Banco')
                                ->maxLength(100),
                            TextInput::make('agency')
                                ->label('Agência')
                                ->maxLength(10),
                            TextInput::make('agency_digit')
                                ->label('Dígito da Agência')
                                ->maxLength(2),
                            TextInput::make('account_number')
                                ->label('Conta')
                                ->maxLength(15),
                            TextInput::make('account_digit')
                                ->label('Dígito da Conta')
                                ->maxLength(2),
                            Select::make('account_type')
                                ->label('Tipo de Conta')
                                ->options(collect(AccountType::cases())->mapWithKeys(
                                    fn (AccountType $type) => [$type->value => $type->label()]
                                )),
                            Select::make('pix_key_type')
                                ->label('Tipo de Chave PIX')
                                ->options(collect(PixKeyType::cases())->mapWithKeys(
                                    fn (PixKeyType $type) => [$type->value => $type->label()]
                                ))
                                ->live(),
                            TextInput::make('pix_key')
                                ->label('Chave PIX')
                                ->maxLength(255)
                                ->visible(fn (Get $get): bool => filled($get('pix_key_type'))),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),
        ]);
    }
}
