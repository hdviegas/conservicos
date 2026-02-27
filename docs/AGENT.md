# CONSERVICOS - Guia de Desenvolvimento para Agente

> **Este documento e a fonte unica de verdade para o desenvolvimento do sistema.**
> Siga cada sprint sequencialmente. Marque os checkboxes conforme concluir cada item.
> Consulte a pasta `docs/` para amostras reais dos CSVs importados pelo sistema.

---

## 1. CONTEXTO E PAPEL DO AGENTE

Voce e um desenvolvedor Laravel senior especializado em sistemas de gestao de RH e folha de pagamento brasileira.

Estamos construindo o **CONSERVICOS** -- um sistema de gestao de pessoas e financas para duas empresas de terceirizacao de servicos pesados (carregamento e lonamento de caminhoes, operacao de empilhadeiras, movimentacao de estoques). As empresas sao:

- **SERVICON SERVICOS LTDA** -- CNPJ 03.225.148/0001-12
- **LARA P R ROCHA PSICOLOGIA E SERVICOS LTDA** -- CNPJ 44.053.313/0001-83

O sistema importa dados de um relogio de ponto via CSV e processa folha de pagamento com calculos de DSR, horas extras (50% e 100%), adicional noturno, banco de horas, controle de faltas/atestados, vale transporte, ferias, exames ocupacionais (NRs), treinamentos obrigatorios e gera arquivos CNAB 240/400 para pagamento via Banco do Brasil e Caixa Economica Federal.

---

## 2. STACK E CONVENCOES

### Stack Tecnologico

| Camada        | Tecnologia                                      |
|---------------|--------------------------------------------------|
| Backend       | PHP 8.3+ / Laravel 12                           |
| Admin Panel   | Filament 5 (Livewire + Blade + Tailwind)        |
| Banco de dados| MySQL 8                                          |
| Filas         | Laravel Queues (database driver)                 |
| Storage       | Local filesystem (atestados, certificados, CNAB) |
| Auth          | Filament Shield (Spatie Permission)              |
| Export        | maatwebsite/excel + barryvdh/laravel-dompdf      |

### Convencoes de Codigo

- Todo codigo (classes, metodos, variaveis, migrations, enums) em **ingles**
- Labels, textos de interface e mensagens de validacao em **portugues BR**
- Usar **Filament Resources** para todos os CRUDs
- Usar **Enums PHP 8.3** (backed enums) para todos os tipos e status
- Logica de negocio em **Services** (nunca em Controllers ou Resources)
- Processamento pesado em **Jobs** com filas
- Migrations com campos **NOT NULL** quando possivel, com defaults apropriados
- **Indices** nos campos mais consultados: `employee_id`, `date`, `cpf`
- **Soft deletes** nos models principais: `Employee`, `Company`
- Nomes de arquivos e classes seguem o padrao Laravel (PascalCase para classes, snake_case para migrations/tabelas)

### Estrutura de Diretorios

```
app/
  Console/Commands/          -> Artisan commands (compliance status updater)
  Enums/                     -> PHP 8.3 backed enums
  Filament/
    Resources/               -> Filament CRUD resources
    Widgets/                 -> Dashboard widgets
    Pages/                   -> Custom pages (Dashboard)
    Imports/                 -> CSV importers
    Exports/                 -> XLSX/PDF exporters
  Models/                    -> Eloquent models
  Services/                  -> Business logic services
  Jobs/                      -> Queue jobs
  Observers/                 -> Model observers
database/
  migrations/
  seeders/
docs/                        -> CSVs de referencia do relogio de ponto
```

---

## 3. REFERENCIA DOS DADOS CSV (pasta `docs/`)

### 3.1 Cadastro de Funcionarios: `person_*.csv`

- **Separador**: `;`
- **Encoding**: UTF-8
- **Exemplo**: `docs/person_20260210231306.csv` (~387 registros)

**Header completo (1a linha)**:
```
id_funcionario;CPF;PIS;Nome;Matrícula;Folha;CTPS;Empresa;Departamento;Cargo;Centro de Custo;Código de Barras do Crachá;RG;Telefone;Ramal;Email;Senha para Acesso Web;Endereço;Bairro;Cidade;UF;CEP;Data de Admissão;Nome do Pai;Nome da Mãe;Data de Nascimento;Nacionalidade;Naturalidade;Crachá;Código para Uso no REP;Senha para Uso no REP;Administrador do REP;Ativo;Gênero (masculino/feminino/outros);Estado Civíl (solteiro/casado/divorciado/uniao estavel/separado);PIN;Data de Início do Banco de Horas;...;Horário de Trabalho;Data de Início do Horário de Trabalho;...;CNH;Categoria da CNH;Vencimento da CNH;Nome Social;Data de Demissão;Observações;Motivo de demissão;...;Telefone de Emergência;Tipo Sanguineo (A+/A−/B+/B−/AB+/AB−/O+/O−)
```

**Mapeamento para a tabela `employees`**:

| Campo CSV                  | Campo DB            | Transformacao                    |
|----------------------------|---------------------|----------------------------------|
| id_funcionario             | external_id         | inteiro                          |
| CPF                        | cpf                 | string, manter sem formatacao    |
| PIS                        | pis                 | string                           |
| Nome                       | name                | string                           |
| Matrícula                  | matricula           | string                           |
| Folha                      | folha               | string nullable                  |
| CTPS                       | ctps                | string nullable                  |
| Empresa                    | company_id          | lookup por nome da empresa       |
| Departamento               | department_id       | lookup por nome do departamento  |
| Cargo                      | position_id         | lookup por nome do cargo         |
| Data de Admissão           | admission_date      | DD/MM/YYYY -> Y-m-d             |
| Data de Nascimento         | birth_date          | DD/MM/YYYY -> Y-m-d nullable    |
| Data de Demissão           | termination_date    | DD/MM/YYYY -> Y-m-d nullable    |
| Horário de Trabalho        | work_schedule_id    | lookup por nome da escala        |
| Ativo                      | active              | "True"/"1" -> true               |
| Nome Social                | social_name         | string nullable                  |
| Gênero                     | gender              | string nullable                  |
| Estado Civíl               | marital_status      | string nullable                  |
| CNH                        | cnh                 | string nullable                  |
| Categoria da CNH           | cnh_category        | string nullable                  |
| Vencimento da CNH          | cnh_expiration      | DD/MM/YYYY -> Y-m-d nullable    |
| Telefone de Emergência     | emergency_phone     | string nullable                  |
| Tipo Sanguineo             | blood_type          | string nullable                  |

### 3.2 Relatorio de Ponto (formato completo): `relatorio_2026210_2218.CSV`

- **Separador**: `;`
- **~1677 linhas** (empresa LARA, janeiro/2026)
- **Inclui colunas Extra 50% e Extra 100%**

**Header**:
```
Nome da empresa;CNPJ da empresa;Inscrição Estadual da empresa;Nome do funcionário;CPF do funcionário;Número da Folha (funcionário);PIS do funcionário;CTPS do funcionário;Data de Admissão do funcionário;Nome do cargo;Número de matrícula;Nome do departamento;Nome do centro de custo;Dia;Entrada 1;Saída 1;Entrada 2;Saída 2;Total Normais;Total Noturno;Dia Falta;Dias Trabalhados;Falta e Atraso;Abono;Extra   50% ;Extra   100% ;Banco Negativo;Justificativas
```

### 3.3 Relatorio de Ponto (formato reduzido): `relatorio_2026210_2249.CSV`

- **Separador**: `;`
- **~14 linhas** (empresa SERVICON, fevereiro/2026)
- **NAO inclui colunas Extra 50% e Extra 100%**

**Header**:
```
Nome da empresa;CNPJ da empresa;Inscrição Estadual da empresa;Nome do funcionário;CPF do funcionário;Número da Folha (funcionário);PIS do funcionário;CTPS do funcionário;Data de Admissão do funcionário;Nome do cargo;Número de matrícula;Nome do departamento;Nome do centro de custo;Dia;Entrada 1;Saída 1;Entrada 2;Saída 2;Total Normais;Total Noturno;Dia Falta;Dias Trabalhados;Falta e Atraso;Abono;Banco Negativo;Justificativas
```

### 3.4 Valores Especiais nos Campos Entrada/Saida

Os campos `Entrada 1`, `Saida 1`, `Entrada 2`, `Saida 2` podem conter:

| Valor no CSV                        | day_type (enum)   | Acao                                   |
|--------------------------------------|-------------------|----------------------------------------|
| `HH:MM` (ex: `05:54`)              | worked            | Parsear como horario                   |
| `HH:MM (I)` (ex: `12:00 (I)`)      | worked            | Remover sufixo `(I)`, parsear horario  |
| `Feriado: Ano novo`                 | holiday           | Extrair nome do feriado apos `:`       |
| `Feriado: Santos Reis`              | holiday           | Extrair nome do feriado apos `:`       |
| `Domingo`                           | sunday            | Sem marcacoes                          |
| `Folga`                             | folga             | Folga de escala (nao e banco de horas) |
| `Justificado Folga BH`             | folga_bh          | Debito no banco de horas               |
| `Justificado Férias`               | vacation          | Periodo de ferias                      |
| `Justificado Atestado Médico`      | medical_leave     | Falta justificada com atestado         |
| `Justificado Licença Casamento`    | wedding_leave     | Licenca legal                          |
| `Falta`                             | absence           | Falta injustificada                    |

### 3.5 Campos Numericos do Relatorio de Ponto

| Campo CSV          | Campo DB              | Formato           | Exemplo       |
|--------------------|-----------------------|--------------------|---------------|
| Total Normais      | total_normal_hours    | ` HH:MM` -> min   | ` 07:20` = 440|
| Total Noturno      | total_night_hours     | ` HH:MM` -> min   | ` 06:51` = 411|
| Dia Falta          | is_absence_day        | ` 1` ou vazio      | bool          |
| Dias Trabalhados   | is_worked_day         | ` 1` ou vazio      | bool          |
| Falta e Atraso     | absence_hours         | ` HH:MM` -> min   | ` 08:30` = 510|
| Abono              | bonus_hours           | ` HH:MM` -> min   | ` 03:26` = 206|
| Extra 50%          | overtime_50           | `HH:MM ` -> min   | `00:19 ` = 19 |
| Extra 100%         | overtime_100          | `HH:MM ` -> min   | `10:36 ` = 636|
| Banco Negativo     | negative_bank_hours   | ` HH:MM` -> min   | `-02:20` = 140|
| Justificativas     | justification         | texto livre        | string        |

> **Atencao**: os valores podem ter espacos antes/depois. Sempre aplicar `trim()` antes de parsear.

---

## 4. DATABASE SCHEMA COMPLETO

### 4.1 Enums

```php
// app/Enums/WorkScheduleType.php
enum WorkScheduleType: string {
    case Regular = 'regular';
    case Scale12x36 = 'scale_12x36';
    case Scale6x2 = 'scale_6x2';
    case Custom = 'custom';
}

// app/Enums/DayType.php
enum DayType: string {
    case Worked = 'worked';
    case Sunday = 'sunday';
    case Holiday = 'holiday';
    case DayOff = 'folga';
    case BankHoursOff = 'folga_bh';
    case Vacation = 'vacation';
    case MedicalLeave = 'medical_leave';
    case WeddingLeave = 'wedding_leave';
    case Absence = 'absence';
    case OtherJustified = 'other_justified';
}

// app/Enums/ImportStatus.php
enum ImportStatus: string {
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}

// app/Enums/ImportType.php
enum ImportType: string {
    case Employees = 'employees';
    case TimeReport = 'time_report';
}

// app/Enums/PayrollStatus.php
enum PayrollStatus: string {
    case Draft = 'draft';
    case Calculating = 'calculating';
    case Calculated = 'calculated';
    case Reviewed = 'reviewed';
    case Closed = 'closed';
}

// app/Enums/AbsenceType.php
enum AbsenceType: string {
    case Unjustified = 'unjustified';
    case MedicalCertificate = 'medical_certificate';
    case WeddingLeave = 'wedding_leave';
    case PaternityLeave = 'paternity_leave';
    case BereavementLeave = 'bereavement_leave';
    case OtherJustified = 'other_justified';
}

// app/Enums/HoursBankType.php
enum HoursBankType: string {
    case Credit = 'credit';
    case Debit = 'debit';
}

// app/Enums/HoursBankSource.php
enum HoursBankSource: string {
    case Import = 'import';
    case Manual = 'manual';
    case BankHoursOff = 'bank_hours_off';
    case OvertimeConversion = 'overtime_conversion';
}

// app/Enums/VacationStatus.php
enum VacationStatus: string {
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Expired = 'expired';
}

// app/Enums/ExamCategory.php
enum ExamCategory: string {
    case OccupationalHealth = 'occupational_health';
    case RegulatoryNorm = 'regulatory_norm';
}

// app/Enums/ExamResult.php
enum ExamResult: string {
    case Fit = 'fit';
    case Unfit = 'unfit';
    case FitWithRestrictions = 'fit_with_restrictions';
}

// app/Enums/ComplianceStatus.php
enum ComplianceStatus: string {
    case Valid = 'valid';
    case Expiring30d = 'expiring_30d';
    case Expiring15d = 'expiring_15d';
    case Expired = 'expired';
    case NotApplicable = 'not_applicable';
}

// app/Enums/TrainingCategory.php
enum TrainingCategory: string {
    case Operational = 'operational';
    case Safety = 'safety';
    case Regulatory = 'regulatory';
    case Onboarding = 'onboarding';
}

// app/Enums/AccountType.php
enum AccountType: string {
    case Checking = 'checking';
    case Savings = 'savings';
}

// app/Enums/PixKeyType.php
enum PixKeyType: string {
    case Cpf = 'cpf';
    case Phone = 'phone';
    case Email = 'email';
    case Random = 'random';
}

// app/Enums/PaymentBatchType.php
enum PaymentBatchType: string {
    case Salary = 'salary';
    case Advance = 'advance';
    case Vacation = 'vacation';
    case Termination = 'termination';
    case TransportVoucher = 'transport_voucher';
    case MealVoucher = 'meal_voucher';
}

// app/Enums/PaymentBatchStatus.php
enum PaymentBatchStatus: string {
    case Draft = 'draft';
    case Generated = 'generated';
    case SentToBank = 'sent_to_bank';
    case Processed = 'processed';
    case Rejected = 'rejected';
}

// app/Enums/PaymentMethod.php
enum PaymentMethod: string {
    case BankTransfer = 'bank_transfer';
    case Pix = 'pix';
}

// app/Enums/PaymentItemStatus.php
enum PaymentItemStatus: string {
    case Pending = 'pending';
    case Paid = 'paid';
    case Rejected = 'rejected';
    case Returned = 'returned';
}

// app/Enums/CnabFormat.php
enum CnabFormat: string {
    case Cnab240 = 'cnab_240';
    case Cnab400 = 'cnab_400';
}
```

### 4.2 Tabelas e Campos

#### `companies`
| Campo               | Tipo              | Restricoes              |
|---------------------|-------------------|--------------------------|
| id                  | bigIncrements     | PK                       |
| name                | string(255)       | NOT NULL                 |
| trade_name          | string(255)       | nullable                 |
| cnpj                | string(18)        | NOT NULL, unique         |
| inscricao_estadual  | string(20)        | nullable                 |
| address             | string(500)       | nullable                 |
| city                | string(100)       | nullable                 |
| state               | char(2)           | nullable                 |
| phone               | string(20)        | nullable                 |
| email               | string(255)       | nullable                 |
| active              | boolean           | NOT NULL, default true   |
| deleted_at          | softDeletes       |                          |
| timestamps          |                   |                          |

#### `departments`
| Campo      | Tipo          | Restricoes                     |
|------------|---------------|--------------------------------|
| id         | bigIncrements | PK                             |
| company_id | foreignId     | FK -> companies, constrained   |
| name       | string(255)   | NOT NULL                       |
| active     | boolean       | NOT NULL, default true         |
| timestamps |               |                                |

#### `positions`
| Campo        | Tipo            | Restricoes              |
|--------------|-----------------|--------------------------|
| id           | bigIncrements   | PK                       |
| name         | string(255)     | NOT NULL                 |
| weekly_hours | decimal(5,2)    | nullable                 |
| base_salary  | decimal(10,2)   | nullable                 |
| description  | text            | nullable                 |
| active       | boolean         | NOT NULL, default true   |
| timestamps   |                 |                          |

#### `work_schedules`
| Campo          | Tipo           | Restricoes                              |
|----------------|----------------|-----------------------------------------|
| id             | bigIncrements  | PK                                      |
| name           | string(255)    | NOT NULL                                |
| type           | string(20)     | NOT NULL, WorkScheduleType enum         |
| daily_hours    | decimal(5,2)   | nullable                                |
| weekly_hours   | decimal(5,2)   | nullable                                |
| start_time     | time           | nullable                                |
| end_time       | time           | nullable                                |
| is_night_shift | boolean        | NOT NULL, default false                 |
| description    | text           | nullable                                |
| active         | boolean        | NOT NULL, default true                  |
| timestamps     |                |                                         |

#### `cities`
| Campo     | Tipo          | Restricoes      |
|-----------|---------------|-----------------|
| id        | bigIncrements | PK              |
| name      | string(255)   | NOT NULL        |
| state     | char(2)       | NOT NULL        |
| ibge_code | string(10)    | nullable        |
| timestamps|               |                 |

#### `holidays`
| Campo     | Tipo          | Restricoes                     |
|-----------|---------------|--------------------------------|
| id        | bigIncrements | PK                             |
| name      | string(255)   | NOT NULL                       |
| date      | date          | NOT NULL                       |
| city_id   | foreignId     | FK -> cities, nullable         |
| recurring | boolean       | NOT NULL, default false        |
| active    | boolean       | NOT NULL, default true         |
| timestamps|               |                                |

#### `employees`
| Campo                 | Tipo           | Restricoes                            |
|-----------------------|----------------|---------------------------------------|
| id                    | bigIncrements  | PK                                    |
| company_id            | foreignId      | FK -> companies, constrained          |
| department_id         | foreignId      | FK -> departments, nullable           |
| position_id           | foreignId      | FK -> positions, nullable             |
| work_schedule_id      | foreignId      | FK -> work_schedules, nullable        |
| name                  | string(255)    | NOT NULL                              |
| social_name           | string(255)    | nullable                              |
| cpf                   | string(14)     | NOT NULL, unique                      |
| pis                   | string(15)     | nullable                              |
| rg                    | string(20)     | nullable                              |
| birth_date            | date           | nullable                              |
| gender                | string(20)     | nullable                              |
| marital_status        | string(30)     | nullable                              |
| nationality           | string(50)     | nullable                              |
| naturality            | string(100)    | nullable                              |
| father_name           | string(255)    | nullable                              |
| mother_name           | string(255)    | nullable                              |
| blood_type            | string(5)      | nullable                              |
| phone                 | string(20)     | nullable                              |
| emergency_phone       | string(20)     | nullable                              |
| email                 | string(255)    | nullable                              |
| address               | string(500)    | nullable                              |
| neighborhood          | string(100)    | nullable                              |
| city                  | string(100)    | nullable                              |
| state                 | char(2)        | nullable                              |
| zip_code              | string(10)     | nullable                              |
| matricula             | string(20)     | nullable                              |
| folha                 | string(20)     | nullable                              |
| ctps                  | string(30)     | nullable                              |
| admission_date        | date           | nullable                              |
| termination_date      | date           | nullable                              |
| termination_reason    | text           | nullable                              |
| cnh                   | string(20)     | nullable                              |
| cnh_category          | string(5)      | nullable                              |
| cnh_expiration        | date           | nullable                              |
| bank_code             | string(5)      | nullable                              |
| bank_name             | string(100)    | nullable                              |
| agency                | string(10)     | nullable                              |
| agency_digit          | string(2)      | nullable                              |
| account_number        | string(15)     | nullable                              |
| account_digit         | string(2)      | nullable                              |
| account_type          | string(10)     | nullable, AccountType enum            |
| pix_key_type          | string(10)     | nullable, PixKeyType enum             |
| pix_key               | string(255)    | nullable                              |
| hours_bank_start_date | date           | nullable                              |
| external_id           | string(20)     | nullable, index                       |
| active                | boolean        | NOT NULL, default true                |
| observations          | text           | nullable                              |
| deleted_at            | softDeletes    |                                       |
| timestamps            |                |                                       |

**Indices**: `cpf` (unique), `external_id`, `company_id`, `department_id`, `position_id`

#### `time_imports`
| Campo            | Tipo          | Restricoes                     |
|------------------|---------------|--------------------------------|
| id               | bigIncrements | PK                             |
| company_id       | foreignId     | FK -> companies                |
| user_id          | foreignId     | FK -> users                    |
| filename         | string(255)   | NOT NULL                       |
| original_filename| string(255)   | NOT NULL                       |
| period_month     | tinyInteger   | NOT NULL                       |
| period_year      | smallInteger  | NOT NULL                       |
| type             | string(20)    | NOT NULL, ImportType enum      |
| records_count    | integer       | NOT NULL, default 0            |
| status           | string(20)    | NOT NULL, ImportStatus enum    |
| errors           | json          | nullable                       |
| imported_at      | timestamp     | nullable                       |
| timestamps       |               |                                |

#### `time_records`
| Campo              | Tipo          | Restricoes                               |
|--------------------|---------------|------------------------------------------|
| id                 | bigIncrements | PK                                       |
| time_import_id     | foreignId     | FK -> time_imports                       |
| employee_id        | foreignId     | FK -> employees, index                   |
| date               | date          | NOT NULL                                 |
| entry_1            | time          | nullable                                 |
| exit_1             | time          | nullable                                 |
| entry_2            | time          | nullable                                 |
| exit_2             | time          | nullable                                 |
| total_normal_hours | integer       | NOT NULL, default 0 (minutos)            |
| total_night_hours  | integer       | NOT NULL, default 0 (minutos)            |
| day_type           | string(20)    | NOT NULL, DayType enum                   |
| is_absence_day     | boolean       | NOT NULL, default false                  |
| is_worked_day      | boolean       | NOT NULL, default false                  |
| absence_hours      | integer       | NOT NULL, default 0 (minutos)            |
| bonus_hours        | integer       | NOT NULL, default 0 (minutos)            |
| overtime_50        | integer       | NOT NULL, default 0 (minutos)            |
| overtime_100       | integer       | NOT NULL, default 0 (minutos)            |
| negative_bank_hours| integer       | NOT NULL, default 0 (minutos)            |
| justification      | text          | nullable                                 |
| holiday_name       | string(100)   | nullable                                 |
| raw_entry_1        | string(100)   | nullable                                 |
| raw_exit_1         | string(100)   | nullable                                 |
| raw_entry_2        | string(100)   | nullable                                 |
| raw_exit_2         | string(100)   | nullable                                 |
| timestamps         |               |                                          |

**Indices**: `[employee_id, date]` unique composto

#### `payroll_periods`
| Campo       | Tipo          | Restricoes                     |
|-------------|---------------|--------------------------------|
| id          | bigIncrements | PK                             |
| company_id  | foreignId     | FK -> companies                |
| month       | tinyInteger   | NOT NULL                       |
| year        | smallInteger  | NOT NULL                       |
| status      | string(20)    | NOT NULL, PayrollStatus enum   |
| calculated_at| timestamp    | nullable                       |
| closed_at   | timestamp     | nullable                       |
| closed_by   | foreignId     | FK -> users, nullable          |
| notes       | text          | nullable                       |
| timestamps  |               |                                |

**Indices**: `[company_id, month, year]` unique

#### `payroll_entries`
| Campo                        | Tipo           | Restricoes                   |
|------------------------------|----------------|------------------------------|
| id                           | bigIncrements  | PK                           |
| payroll_period_id            | foreignId      | FK -> payroll_periods        |
| employee_id                  | foreignId      | FK -> employees              |
| total_worked_days            | integer        | NOT NULL, default 0          |
| total_absence_days           | integer        | NOT NULL, default 0          |
| total_justified_absence_days | integer        | NOT NULL, default 0          |
| total_sundays                | integer        | NOT NULL, default 0          |
| total_holidays               | integer        | NOT NULL, default 0          |
| total_folgas                 | integer        | NOT NULL, default 0          |
| total_normal_hours           | integer        | NOT NULL, default 0 (min)    |
| total_night_hours            | integer        | NOT NULL, default 0 (min)    |
| overtime_50_hours            | integer        | NOT NULL, default 0 (min)    |
| overtime_50_value            | decimal(10,2)  | NOT NULL, default 0          |
| overtime_100_hours           | integer        | NOT NULL, default 0 (min)    |
| overtime_100_value           | decimal(10,2)  | NOT NULL, default 0          |
| night_differential_hours     | integer        | NOT NULL, default 0 (min)    |
| night_differential_value     | decimal(10,2)  | NOT NULL, default 0          |
| dsr_base_value               | decimal(10,2)  | NOT NULL, default 0          |
| dsr_discount_value           | decimal(10,2)  | NOT NULL, default 0          |
| dsr_final_value              | decimal(10,2)  | NOT NULL, default 0          |
| transport_voucher_days       | integer        | NOT NULL, default 0          |
| transport_voucher_daily_value| decimal(8,2)   | NOT NULL, default 0          |
| transport_voucher_total      | decimal(10,2)  | NOT NULL, default 0          |
| hours_bank_balance           | integer        | NOT NULL, default 0 (min)    |
| hours_bank_credit            | integer        | NOT NULL, default 0 (min)    |
| hours_bank_debit             | integer        | NOT NULL, default 0 (min)    |
| base_salary                  | decimal(10,2)  | NOT NULL, default 0          |
| gross_additions              | decimal(10,2)  | NOT NULL, default 0          |
| gross_deductions             | decimal(10,2)  | NOT NULL, default 0          |
| vacation_notes               | text           | nullable                     |
| inss_notes                   | text           | nullable                     |
| termination_notes            | text           | nullable                     |
| observations                 | text           | nullable                     |
| timestamps                   |                |                              |

**Indices**: `[payroll_period_id, employee_id]` unique

#### `transport_vouchers`
| Campo       | Tipo          | Restricoes              |
|-------------|---------------|--------------------------|
| id          | bigIncrements | PK                       |
| employee_id | foreignId     | FK -> employees          |
| month       | tinyInteger   | NOT NULL                 |
| year        | smallInteger  | NOT NULL                 |
| daily_value | decimal(8,2)  | NOT NULL, default 0      |
| total_days  | integer       | NOT NULL, default 0      |
| total_value | decimal(10,2) | NOT NULL, default 0      |
| notes       | text          | nullable                 |
| timestamps  |               |                          |

#### `vacations`
| Campo                    | Tipo          | Restricoes                  |
|--------------------------|---------------|-----------------------------|
| id                       | bigIncrements | PK                          |
| employee_id              | foreignId     | FK -> employees             |
| acquisition_period_start | date          | NOT NULL                    |
| acquisition_period_end   | date          | NOT NULL                    |
| scheduled_start          | date          | nullable                    |
| scheduled_end            | date          | nullable                    |
| actual_start             | date          | nullable                    |
| actual_end               | date          | nullable                    |
| days_enjoyed             | integer       | NOT NULL, default 0         |
| days_sold                | integer       | NOT NULL, default 0         |
| status                   | string(20)    | NOT NULL, VacationStatus    |
| notes                    | text          | nullable                    |
| timestamps               |               |                             |

#### `absences`
| Campo              | Tipo          | Restricoes                     |
|--------------------|---------------|--------------------------------|
| id                 | bigIncrements | PK                             |
| employee_id        | foreignId     | FK -> employees, index         |
| date               | date          | NOT NULL                       |
| type               | string(30)    | NOT NULL, AbsenceType enum     |
| justified          | boolean       | NOT NULL, default false        |
| justification_text | text          | nullable                       |
| attachment_path    | string(500)   | nullable                       |
| cid_code           | string(10)    | nullable                       |
| days_count         | integer       | NOT NULL, default 1            |
| notes              | text          | nullable                       |
| timestamps         |               |                                |

#### `hours_bank_entries`
| Campo                    | Tipo          | Restricoes                       |
|--------------------------|---------------|----------------------------------|
| id                       | bigIncrements | PK                               |
| employee_id              | foreignId     | FK -> employees, index           |
| date                     | date          | NOT NULL                         |
| type                     | string(10)    | NOT NULL, HoursBankType enum     |
| minutes                  | integer       | NOT NULL                         |
| source                   | string(25)    | NOT NULL, HoursBankSource enum   |
| description              | text          | nullable                         |
| reference_time_record_id | foreignId     | FK -> time_records, nullable     |
| timestamps               |               |                                  |

#### `exams`
| Campo               | Tipo          | Restricoes                     |
|---------------------|---------------|--------------------------------|
| id                  | bigIncrements | PK                             |
| name                | string(255)   | NOT NULL                       |
| description         | text          | nullable                       |
| category            | string(25)    | NOT NULL, ExamCategory enum    |
| nr_reference        | string(10)    | nullable                       |
| validity_months     | integer       | NOT NULL, default 0            |
| is_mandatory        | boolean       | NOT NULL, default false        |
| requires_attachment | boolean       | NOT NULL, default false        |
| active              | boolean       | NOT NULL, default true         |
| timestamps          |               |                                |

#### `employee_exams`
| Campo           | Tipo          | Restricoes                       |
|-----------------|---------------|----------------------------------|
| id              | bigIncrements | PK                               |
| employee_id     | foreignId     | FK -> employees, index           |
| exam_id         | foreignId     | FK -> exams                      |
| performed_date  | date          | NOT NULL                         |
| expiration_date | date          | nullable                         |
| provider        | string(255)   | nullable                         |
| doctor_name     | string(255)   | nullable                         |
| crm             | string(20)    | nullable                         |
| status          | string(20)    | NOT NULL, ComplianceStatus enum  |
| result          | string(25)    | nullable, ExamResult enum        |
| restrictions    | text          | nullable                         |
| attachment_path | string(500)   | nullable                         |
| notes           | text          | nullable                         |
| timestamps      |               |                                  |

#### `trainings`
| Campo               | Tipo          | Restricoes                       |
|---------------------|---------------|----------------------------------|
| id                  | bigIncrements | PK                               |
| name                | string(255)   | NOT NULL                         |
| description         | text          | nullable                         |
| category            | string(20)    | NOT NULL, TrainingCategory enum  |
| nr_reference        | string(10)    | nullable                         |
| validity_months     | integer       | NOT NULL, default 0              |
| required_hours      | integer       | nullable                         |
| is_mandatory        | boolean       | NOT NULL, default false          |
| requires_certificate| boolean       | NOT NULL, default false          |
| active              | boolean       | NOT NULL, default true           |
| timestamps          |               |                                  |

#### `employee_trainings`
| Campo            | Tipo          | Restricoes                       |
|------------------|---------------|----------------------------------|
| id               | bigIncrements | PK                               |
| employee_id      | foreignId     | FK -> employees, index           |
| training_id      | foreignId     | FK -> trainings                  |
| performed_date   | date          | NOT NULL                         |
| expiration_date  | date          | nullable                         |
| instructor_name  | string(255)   | nullable                         |
| institution      | string(255)   | nullable                         |
| hours_completed  | integer       | nullable                         |
| status           | string(20)    | NOT NULL, ComplianceStatus enum  |
| grade            | string(10)    | nullable                         |
| attachment_path  | string(500)   | nullable                         |
| certificate_path | string(500)   | nullable                         |
| notes            | text          | nullable                         |
| timestamps       |               |                                  |

#### `compliance_position_requirements`
| Campo            | Tipo          | Restricoes                     |
|------------------|---------------|--------------------------------|
| id               | bigIncrements | PK                             |
| position_id      | foreignId     | FK -> positions                |
| requireable_type | string(255)   | NOT NULL (morph)               |
| requireable_id   | unsignedBigInt| NOT NULL (morph)               |
| is_mandatory     | boolean       | NOT NULL, default true         |
| timestamps       |               |                                |

**Indice morph**: `[requireable_type, requireable_id]`

#### `company_bank_accounts`
| Campo          | Tipo          | Restricoes                     |
|----------------|---------------|--------------------------------|
| id             | bigIncrements | PK                             |
| company_id     | foreignId     | FK -> companies                |
| bank_code      | string(5)     | NOT NULL                       |
| bank_name      | string(100)   | NOT NULL                       |
| agency         | string(10)    | NOT NULL                       |
| agency_digit   | string(2)     | nullable                       |
| account_number | string(15)    | NOT NULL                       |
| account_digit  | string(2)     | nullable                       |
| account_type   | string(10)    | NOT NULL, AccountType enum     |
| covenant_code  | string(20)    | nullable                       |
| is_default     | boolean       | NOT NULL, default false        |
| active         | boolean       | NOT NULL, default true         |
| timestamps     |               |                                |

#### `payment_batches`
| Campo            | Tipo           | Restricoes                        |
|------------------|----------------|-----------------------------------|
| id               | bigIncrements  | PK                                |
| company_id       | foreignId      | FK -> companies                   |
| type             | string(25)     | NOT NULL, PaymentBatchType enum   |
| reference_month  | tinyInteger    | NOT NULL                          |
| reference_year   | smallInteger   | NOT NULL                          |
| payment_date     | date           | NOT NULL                          |
| bank_code        | string(5)      | NOT NULL                          |
| total_amount     | decimal(12,2)  | NOT NULL, default 0               |
| total_records    | integer        | NOT NULL, default 0               |
| status           | string(20)     | NOT NULL, PaymentBatchStatus enum |
| cnab_format      | string(10)     | NOT NULL, CnabFormat enum         |
| file_path        | string(500)    | nullable                          |
| return_file_path | string(500)    | nullable                          |
| notes            | text           | nullable                          |
| generated_by     | foreignId      | FK -> users, nullable             |
| generated_at     | timestamp      | nullable                          |
| timestamps       |                |                                   |

#### `payment_batch_items`
| Campo            | Tipo           | Restricoes                         |
|------------------|----------------|------------------------------------|
| id               | bigIncrements  | PK                                 |
| payment_batch_id | foreignId      | FK -> payment_batches              |
| employee_id      | foreignId      | FK -> employees                    |
| amount           | decimal(10,2)  | NOT NULL                           |
| payment_method   | string(15)     | NOT NULL, PaymentMethod enum       |
| bank_code        | string(5)      | nullable                           |
| agency           | string(10)     | nullable                           |
| agency_digit     | string(2)      | nullable                           |
| account_number   | string(15)     | nullable                           |
| account_digit    | string(2)      | nullable                           |
| account_type     | string(10)     | nullable                           |
| pix_key          | string(255)    | nullable                           |
| status           | string(15)     | NOT NULL, PaymentItemStatus enum   |
| rejection_reason | string(255)    | nullable                           |
| reference_id     | unsignedBigInt | nullable                           |
| notes            | text           | nullable                           |
| timestamps       |                |                                    |

---

## 5. REGRAS DE NEGOCIO

### 5.1 DSR (Descanso Semanal Remunerado)

- Calculado com base nas folgas da semana
- Faltas injustificadas na semana afetam o DSR proporcional
- Formula: `DSR = (Salario / dias_uteis_mes) * domingos_e_feriados`
- Desconto DSR por falta: `Desconto = (valor_DSR / dias_uteis_semana) * faltas_semana`

### 5.2 Horas Extras

- **50%**: Horas excedentes em dias normais que nao viram banco de horas; pagas apos 30 dias caso nao haja gozo de folga
- **100%**: Trabalho em domingos ou feriados, pagamento integral
- Campos ja vem calculados no CSV do ponto (`Extra 50%`, `Extra 100%`)

### 5.3 Adicional Noturno

- Ja vem calculado no CSV do ponto (`Total Noturno`)
- Transferido direto para a planilha da folha
- Periodo noturno: 22h as 05h (hora noturna = 52min30s)
- Percentual: 20% sobre o valor da hora normal

### 5.4 Banco de Horas

- Horas extras que nao sao pagas viram credito no banco
- Folga BH: debito no banco quando o funcionario folga
- Banco negativo ja vem no CSV
- Controle de saldo com historico de movimentacoes
- Apos 30 dias sem gozo de folga, horas extras sao pagas

### 5.5 Escalas 12x36

- Dias livres lancados como "Folga" no ponto, mas nao sao folgas reais (sao dias de descanso da escala)
- Diferenciar: "Folga" de escala vs "Folga BH" vs "Folga" real

### 5.6 Vale Transporte

- Calculado com base nos dias efetivamente trabalhados (campo `Dias Trabalhados` do CSV)
- Lancamento mensal para registro

### 5.7 Compliance (Exames e Treinamentos)

**NRs aplicaveis**:
- NR-06 (EPIs): validade 12 meses
- NR-11 (Empilhadeira/Transporte de Materiais): validade 12 meses
- NR-12 (Seguranca em Maquinas): validade 12 meses
- NR-35 (Trabalho em Altura): validade 24 meses
- ASO (Exame Periodico): validade 12 meses

**Regras de status** (recalculadas diariamente via `app:update-compliance-status`):
- `valid`: vencimento > 30 dias
- `expiring_30d`: vencimento entre 16 e 30 dias
- `expiring_15d`: vencimento entre 1 e 15 dias
- `expired`: vencimento ultrapassado

**Exigencias por cargo**:
- OPERADOR EMPILHADEIRA: ASO, NR-11, NR-12, Treinamento Empilhadeira
- AJUDANTE DE CARGAS: ASO, NR-11, NR-35, NR-06, Movimentacao Manual de Cargas, Seguranca do Trabalho
- ANALISTA DE LOGISTICA: ASO, NR-06, Integracao
- CONFERENTE: ASO, NR-06, NR-11, Seguranca do Trabalho

### 5.8 CNAB (Pagamentos Bancarios)

- Bancos: Banco do Brasil (001) e Caixa Economica Federal (104)
- Formatos: CNAB 240 e CNAB 400
- PIX via Segmento A, forma de pagamento 45
- Valores sem pontos/virgulas, com zeros a esquerda
- Fluxo: draft -> generated -> sent_to_bank -> processed

---

## 6. SPRINTS DE DESENVOLVIMENTO

---

### SPRINT 1 -- Setup e Fundacao

**Objetivo**: Criar o projeto Laravel 12 com Filament 5 e os CRUDs base.

**Comandos iniciais**:
```bash
composer create-project laravel/laravel conservicos
cd conservicos
composer require filament/filament:"^5.0"
composer require spatie/laravel-permission
composer require bezhansalleh/filament-shield
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
php artisan filament:install --panels
php artisan shield:install
```

**Instrucoes detalhadas**:

Crie o projeto Laravel 12 com Filament 5. Configure:
1. Filament Panel com tema customizado (cores da marca)
2. Spatie Permission com Filament Shield
3. Migrations e Models para: `companies`, `departments`, `positions`, `work_schedules`, `cities`, `holidays`
4. Filament Resources com forms e tables para cada model
5. Seeders com os dados reais das empresas: SERVICON (CNPJ 03225148000112) e LARA (CNPJ 44053313000183), departamentos (RIOGRANDENSE, CD MDIAS, MOINHO RIBEIRA), e cargos (AJUDANTE DE CARGAS, OPERADOR EMPILHADEIRA, ANALISTA DE LOGISTICA, CONFERENTE, AUXILIAR DE ESCRITORIO, AUXILIAR DE PATIO)
6. Enum `WorkScheduleType` (regular, scale_12x36, scale_6x2, custom)
7. Cadastro de feriados com suporte a feriados nacionais e municipais (por cidade)

**Arquivos a criar**:
- `app/Models/Company.php`, `Department.php`, `Position.php`, `WorkSchedule.php`, `City.php`, `Holiday.php`
- `app/Enums/WorkScheduleType.php`
- `app/Filament/Resources/CompanyResource.php` (e demais)
- `database/migrations/` para cada tabela
- `database/seeders/CompanySeeder.php`, `DepartmentSeeder.php`, `PositionSeeder.php`, `WorkScheduleSeeder.php`, `HolidaySeeder.php`

**Checklist**:
- [ ] Projeto Laravel 12 criado e rodando
- [ ] Filament 5 instalado e acessivel em `/admin`
- [ ] Spatie Permission + Shield configurados
- [ ] Migration e Model `companies` com soft deletes
- [ ] Migration e Model `departments` com FK para company
- [ ] Migration e Model `positions` com weekly_hours e base_salary
- [ ] Migration e Model `work_schedules` com enum type
- [ ] Migration e Model `cities`
- [ ] Migration e Model `holidays` com FK nullable para city
- [ ] Filament Resource para cada model com forms e tables funcionais
- [ ] Seeders rodando com `php artisan db:seed`
- [ ] Usuario admin criado e logando no painel

---

### SPRINT 2 -- Gestao de Funcionarios

**Objetivo**: Modulo completo de funcionarios com import CSV.

**Instrucoes detalhadas**:

1. Migration `employees` com todos os campos conforme schema (secao 4.2)
2. Model `Employee` com relacionamentos (`company`, `department`, `position`, `workSchedule`) e scopes (`active`, `terminated`, `byCompany`, `byDepartment`)
3. Filament `EmployeeResource` com form em tabs:
   - Tab "Dados Pessoais": name, social_name, cpf, pis, rg, birth_date, gender, marital_status, nationality, naturality, father_name, mother_name, blood_type
   - Tab "Contato": phone, emergency_phone, email, address, neighborhood, city, state, zip_code
   - Tab "Registro": matricula, folha, ctps, admission_date, termination_date, termination_reason, company, department, position, work_schedule
   - Tab "Documentos": cnh, cnh_category, cnh_expiration
   - Tab "Dados Bancarios": bank_code (Select com 001-BB, 104-CEF e outros), bank_name (auto), agency, agency_digit, account_number, account_digit, account_type, pix_key_type, pix_key
4. Tabela com colunas essenciais e filtros por empresa/departamento/cargo/status
5. CSV Importer conforme mapeamento da secao 3.1 (formato `person_*.csv`, separador `;`)

**Checklist**:
- [ ] Migration `employees` com todos os campos
- [ ] Model `Employee` com relacionamentos e scopes
- [ ] Filament EmployeeResource com 5 tabs funcionais
- [ ] Filtros na tabela: empresa, departamento, cargo, ativo/demitido
- [ ] CSV Importer funcional para `person_*.csv`
- [ ] Importar o arquivo `docs/person_20260210231306.csv` com sucesso

---

### SPRINT 3 -- Importacao de Ponto

**Objetivo**: Importar relatorios CSV do relogio de ponto e armazenar registros diarios.

**Instrucoes detalhadas**:

1. Migrations: `time_imports` e `time_records` conforme schema (secao 4.2)
2. Service `TimeReportImporterService` que:
   - Detecta o formato do CSV (com ou sem colunas `Extra 50%`/`Extra 100%` -- ver secao 3.2 vs 3.3)
   - Parseia o campo `Dia` no formato `"DD/MM/YYYY DDD"` (ex: `"01/01/2026 QUI"`)
   - Identifica `day_type` conforme tabela da secao 3.4
   - Converte horas `HH:MM` para minutos inteiros (aplicar `trim()`)
   - Trata timestamps com sufixo `(I)` removendo o sufixo
   - Vincula ao employee via CPF
   - Salva `raw_entry_*` com os valores originais do CSV
3. Job `ProcessTimeReportImport` para processamento em fila
4. Filament `TimeImportResource`: upload de CSV, preview, botao processar, historico com status
5. Filament `TimeRecordResource` somente leitura: tabela com registros diarios, filtros por funcionario/periodo/tipo de dia, cores por `day_type`

**Checklist**:
- [ ] Migration e Model `TimeImport`
- [ ] Migration e Model `TimeRecord` com indice composto `[employee_id, date]`
- [ ] Enum `DayType`, `ImportStatus`, `ImportType`
- [ ] Service `TimeReportImporterService` com deteccao de formato
- [ ] Parser de valores especiais (Feriado, Domingo, Folga, Ferias, Atestado, Falta, etc.)
- [ ] Conversao `HH:MM` -> minutos com `trim()`
- [ ] Tratamento de sufixo `(I)` nos horarios
- [ ] Job `ProcessTimeReportImport`
- [ ] Filament TimeImportResource com upload e historico
- [ ] Filament TimeRecordResource com filtros e badges coloridos
- [ ] Importar `docs/relatorio_2026210_2218.CSV` (formato completo) com sucesso
- [ ] Importar `docs/relatorio_2026210_2249.CSV` (formato reduzido) com sucesso

---

### SPRINT 4 -- Faltas, Atestados e Banco de Horas

**Objetivo**: Registrar faltas com atestados e controlar o banco de horas.

**Instrucoes detalhadas**:

1. Migration `absences` conforme schema
2. Migration `hours_bank_entries` conforme schema
3. Service `HoursBankService` com metodos:
   - `addCredit(Employee, date, minutes, source, description)`
   - `addDebit(Employee, date, minutes, source, description)`
   - `getBalance(Employee): int` (saldo em minutos)
   - `processTimeRecords(TimeImport)`: cria entradas automaticamente a partir dos `time_records` -- overtime vira credito, folga_bh vira debito, banco negativo vira debito
4. Observer `TimeRecordObserver` que ao criar `time_records`:
   - Cria `absences` quando `day_type = absence`
   - Cria `hours_bank_entries` quando ha overtime, folga_bh ou banco negativo
5. Filament `AbsenceResource` com upload de atestado e filtros
6. Filament `HoursBankEntryResource` com saldo atual e historico, filtrado por funcionario

**Checklist**:
- [ ] Migration e Model `Absence` com enum `AbsenceType`
- [ ] Migration e Model `HoursBankEntry` com enums `HoursBankType`, `HoursBankSource`
- [ ] Service `HoursBankService` com todos os metodos
- [ ] Observer `TimeRecordObserver` criando absences e hours_bank_entries automaticamente
- [ ] Filament AbsenceResource com upload de atestado
- [ ] Filament HoursBankEntryResource com saldo e historico
- [ ] Ao importar ponto, faltas e banco de horas sao populados automaticamente

---

### SPRINT 5 -- Folha de Pagamento

**Objetivo**: Motor de calculo da folha com export para o contador.

**Instrucoes detalhadas**:

1. Migrations: `payroll_periods` e `payroll_entries` conforme schema
2. Service `PayrollCalculatorService`:
   - Metodo `calculate(PayrollPeriod)`: itera funcionarios ativos da empresa, busca `time_records` do periodo, calcula cada `payroll_entry`
   - Calculo DSR: `(salario_base / dias_uteis) * (domingos + feriados)` -- desconto proporcional por faltas injustificadas na semana
   - Horas extras: soma `overtime_50` e `overtime_100` dos `time_records`, aplica percentuais sobre valor hora
   - Adicional noturno: soma `total_night_hours`, aplica 20% sobre valor hora
   - VT: conta dias trabalhados, multiplica por valor diario do funcionario
   - Banco de horas: saldo atual do `HoursBankService`
3. Service `DsrCalculatorService` separado com a logica detalhada do DSR
4. Filament `PayrollPeriodResource`: criar periodo, botao "Calcular Folha", status visual, botao "Fechar Periodo"
5. Filament `PayrollEntryResource`: tabela com todos os totais por funcionario, filtros, export XLSX
6. Export XLSX no formato esperado pelo contador

**Checklist**:
- [ ] Migration e Model `PayrollPeriod` com enum `PayrollStatus`
- [ ] Migration e Model `PayrollEntry` com indice composto
- [ ] Service `PayrollCalculatorService` com metodo `calculate()`
- [ ] Service `DsrCalculatorService`
- [ ] Calculo DSR funcional com desconto por faltas
- [ ] Calculo de horas extras 50% e 100%
- [ ] Calculo de adicional noturno (20%)
- [ ] Calculo de VT por dias trabalhados
- [ ] Saldo de banco de horas integrado
- [ ] Filament PayrollPeriodResource com fluxo completo
- [ ] Filament PayrollEntryResource com tabela e filtros
- [ ] Export XLSX funcional

---

### SPRINT 6 -- Beneficios e Compliance

**Objetivo**: VT, ferias, exames ocupacionais, treinamentos e alertas de vencimento.

**Instrucoes detalhadas**:

**PARTE A -- VT e Ferias**:
1. Migrations para `transport_vouchers` e `vacations`
2. Service `TransportVoucherService`: calcula VT mensal baseado nos dias trabalhados do `time_records`, permite lancamento manual
3. Filament `VacationResource`: controle de periodo aquisitivo (12 meses), agendamento, registro de gozo, dias vendidos, alertas de vencimento

**PARTE B -- Exames Ocupacionais**:
4. Migration `exams` e `employee_exams` conforme schema
5. Filament `ExamResource`: CRUD com campos de NR, validade, obrigatoriedade
6. Filament `EmployeeExamResource`: form com data, clinica, medico, CRM, resultado (apto/inapto/apto com restricoes), upload ASO. Tabela com status visual (badges coloridos)
7. Seeder `ExamSeeder` com: Admissional, Periodico ASO (12m), Demissional, Retorno ao Trabalho, Mudanca de Funcao, NR-11 (12m), NR-12 (12m), NR-35 (24m), NR-06 (12m)

**PARTE C -- Treinamentos**:
8. Migration `trainings` e `employee_trainings` conforme schema
9. Filament `TrainingResource` e `EmployeeTrainingResource` com upload de certificado e status visual
10. Seeder `TrainingSeeder` com: Operacao Empilhadeira (12m, NR-11), Movimentacao Manual de Cargas (12m), Seguranca do Trabalho (12m), Brigada de Incendio/CIPA (12m, NR-23), Primeiros Socorros (24m), Integracao (unico)

**PARTE D -- Exigencias por Cargo e Automacao**:
11. Migration `compliance_position_requirements` (morph pivot)
12. Na edicao do Cargo (`PositionResource`), adicionar Repeater/RelationManager para configurar exames e treinamentos obrigatorios
13. Artisan Command `app:update-compliance-status` que recalcula status de todos `employee_exams` e `employee_trainings`. Registrar no Scheduler para rodar diariamente.
14. Na ficha do funcionario (`EmployeeResource`), adicionar aba "Compliance" com RelationManagers

**PARTE E -- Dashboard de Compliance**:
15. Widget `ComplianceOverviewStats`: 3 cards (Vencidos vermelho, Vencendo 15d laranja, Vencendo 30d amarelo)
16. Widget `ComplianceAlertTable`: tabela de funcionarios pendentes ordenada por urgencia

**Checklist**:
- [ ] Migration e Model `TransportVoucher`
- [ ] Migration e Model `Vacation` com enum `VacationStatus`
- [ ] Service `TransportVoucherService`
- [ ] Filament VacationResource com periodo aquisitivo
- [ ] Migration e Model `Exam`, `EmployeeExam`
- [ ] Migration e Model `Training`, `EmployeeTraining`
- [ ] Enums: `ExamCategory`, `ExamResult`, `ComplianceStatus`, `TrainingCategory`
- [ ] Filament ExamResource e EmployeeExamResource com badges
- [ ] Filament TrainingResource e EmployeeTrainingResource com upload certificado
- [ ] Seeders de exames e treinamentos
- [ ] Migration e Model `CompliancePositionRequirement`
- [ ] PositionResource com configuracao de exigencias por cargo
- [ ] Seeder `ComplianceRequirementSeeder`
- [ ] Command `app:update-compliance-status` registrado no Scheduler
- [ ] Aba "Compliance" no EmployeeResource
- [ ] Widget `ComplianceOverviewStats` no dashboard
- [ ] Widget `ComplianceAlertTable` no dashboard

---

### SPRINT 7 -- Pagamentos Bancarios

**Objetivo**: Gerar arquivos CNAB 240/400 para pagamento via BB e Caixa.

**Instrucoes detalhadas**:

**PARTE A -- Contas Bancarias da Empresa**:
1. Migration `company_bank_accounts` conforme schema
2. Filament `CompanyBankAccountResource` vinculado a empresa
3. Seeder com contas do BB (001) e Caixa (104) para as duas empresas

**PARTE B -- Lotes de Pagamento**:
4. Migrations `payment_batches` e `payment_batch_items` conforme schema
5. Filament `PaymentBatchResource`:
   - Criar lote: selecionar tipo, empresa, mes/ano, data pagamento, banco/conta, formato CNAB
   - Para tipo "salary": popular automaticamente com funcionarios do `payroll_entries` do periodo (so se folha calculada)
   - Para tipo "transport_voucher": popular com dados do `transport_vouchers`
   - Revisao: tabela editavel com todos os itens
   - Botao "Gerar Arquivo CNAB", download, "Marcar como Enviado"
   - Upload de arquivo retorno do banco
6. Filament `PaymentBatchItemResource` como RelationManager dentro do Batch

**PARTE C -- Gerador CNAB**:
7. Service `CnabGeneratorService`:
   - Metodo `generate(PaymentBatch)`: gera arquivo conforme formato e banco
   - CNAB 240: Header de arquivo, Header de lote, Segmento A, Segmento B, Trailer de lote, Trailer de arquivo
   - CNAB 400: Header, Detalhe, Trailer
   - Layouts especificos para BB (001) e CEF (104)
   - PIX no Segmento A (forma de pagamento 45)
   - Valores formatados sem pontos/virgulas, com zeros a esquerda
8. Service `CnabReturnReaderService`: le arquivo retorno (.RET) e atualiza status dos itens

**PARTE D -- Validacoes**:
9. Enums: `PaymentBatchType`, `PaymentBatchStatus`, `PaymentMethod`, `CnabFormat`, `PaymentItemStatus`
10. Validacao: ao criar lote tipo "salary", verificar se `payroll_period` esta calculado/fechado
11. Validacao: ao gerar CNAB, verificar se todos os funcionarios tem dados bancarios preenchidos

**Checklist**:
- [ ] Migration e Model `CompanyBankAccount`
- [ ] Migration e Model `PaymentBatch`
- [ ] Migration e Model `PaymentBatchItem`
- [ ] Enums de pagamento (6 enums)
- [ ] Filament CompanyBankAccountResource
- [ ] Filament PaymentBatchResource com fluxo completo
- [ ] Filament PaymentBatchItemResource como RelationManager
- [ ] Service `CnabGeneratorService` com CNAB 240 e 400
- [ ] Suporte a BB (001) e CEF (104)
- [ ] Suporte a PIX no CNAB
- [ ] Service `CnabReturnReaderService`
- [ ] Validacao de dados bancarios antes de gerar CNAB
- [ ] Validacao de status da folha antes de criar lote de salario
- [ ] Download do arquivo CNAB gerado

---

### SPRINT 8 -- Dashboard e Relatorios

**Objetivo**: Dashboard com visao geral e sistema de relatorios exportaveis.

**Instrucoes detalhadas**:

1. Dashboard Filament com widgets:
   - `StatsOverviewWidget`: total funcionarios ativos, horas extras do mes, faltas do mes, exames vencendo
   - `MonthlyOvertimeChart`: grafico de barras com horas extras 50% e 100% dos ultimos 6 meses
   - `AbsencesChart`: grafico de faltas por tipo dos ultimos 6 meses
   - `UpcomingVacationsTable`: ferias programadas para os proximos 30 dias
   - `ComplianceAlertTable`: exames e treinamentos vencendo (ja criado na Sprint 6)
   - `PendingPaymentsWidget`: lotes de pagamento em status draft ou generated
2. Pagina de Relatorios com exports:
   - Folha de pagamento mensal (XLSX formato contador)
   - Resumo de horas extras por funcionario (XLSX)
   - Relatorio de banco de horas com saldos (XLSX)
   - Relatorio de faltas e atestados (XLSX/PDF)
   - Relatorio de VT mensal (XLSX)
   - Relatorio de exames/treinamentos vencendo (XLSX/PDF)
   - Relatorio de pagamentos bancarios por lote/periodo (XLSX)
3. Todos os exports usando Filament Export ou `maatwebsite/excel`

**Checklist**:
- [ ] Widget `StatsOverviewWidget` com 4 cards
- [ ] Widget `MonthlyOvertimeChart`
- [ ] Widget `AbsencesChart`
- [ ] Widget `UpcomingVacationsTable`
- [ ] Widget `PendingPaymentsWidget`
- [ ] Dashboard configurado com todos os widgets
- [ ] Export folha de pagamento XLSX
- [ ] Export horas extras XLSX
- [ ] Export banco de horas XLSX
- [ ] Export faltas e atestados XLSX/PDF
- [ ] Export VT mensal XLSX
- [ ] Export compliance XLSX/PDF
- [ ] Export pagamentos bancarios XLSX
- [ ] Pagina de relatorios no Filament

---

## 7. DADOS PARA SEEDERS

### Empresas
| Nome                                           | CNPJ               | IE      |
|------------------------------------------------|---------------------|---------|
| SERVICON SERVICOS LTDA                         | 03225148000112      | INSENTO |
| LARA P R ROCHA PSICOLOGIA E SERVICOS LTDA     | 44053313000183      | INSENTO |

### Departamentos
| Departamento    | Empresa  |
|-----------------|----------|
| RIOGRANDENSE    | SERVICON |
| CD MDIAS        | SERVICON |
| MOINHO RIBEIRA  | LARA     |

### Cargos
| Cargo                   | Carga Horaria Semanal |
|-------------------------|-----------------------|
| AJUDANTE DE CARGAS      | 44h                   |
| OPERADOR EMPILHADEIRA   | 44h                   |
| ANALISTA DE LOGISTICA   | 44h                   |
| CONFERENTE              | 44h                   |
| AUXILIAR DE ESCRITORIO  | 44h                   |
| AUXILIAR DE PATIO       | 44h                   |

### Escalas de Trabalho
| Nome                                                    | Tipo        | Noturno |
|---------------------------------------------------------|-------------|---------|
| 6H AS 14:20H SEG A SAB (MOINHO)                       | regular     | nao     |
| 13:40H AS 22H SEG A SAB (MOINHO)                      | regular     | sim     |
| 21:45 AS 06:33H (CD M DIAS)                           | regular     | sim     |
| NOITE - ESCALA 12X36 - IMPAR (MOINHO)                 | scale_12x36 | sim     |
| NOITE - ESCALA 12X36 - PAR (MOINHO)                   | scale_12x36 | sim     |
| ESCALA 6X2 13H AS 21:15                               | scale_6x2   | nao     |
| 08H AS 17H SEG A SEXT E SAB 08H AS 12H (CD M DIAS)   | regular     | nao     |
| 20H (RIOGRANDENSE)                                     | regular     | sim     |
| 7H (RIOGRANDENSE)                                      | regular     | nao     |
| 8H (RIOGRANDENSE)                                      | regular     | nao     |
| 07H ADM (RIOGRANDENSE)                                 | regular     | nao     |
| 6H QUALIDADE (RIOGRANDENSE)                            | regular     | nao     |
| 08H AS 18H SEG A QUI E 08H AS 17H SEXT (CD M DIAS)   | regular     | nao     |
| 13:45H AS 22H SEG A SAB (MOINHO)                      | regular     | sim     |
| 21:45H AS 06:33H SEG A SEXT (MOINHO)                  | regular     | sim     |

### Exames (Seeder)
| Nome                              | Categoria            | NR    | Validade |
|-----------------------------------|----------------------|-------|----------|
| Exame Admissional                 | occupational_health  | -     | 0 meses  |
| Exame Periodico (ASO)            | occupational_health  | -     | 12 meses |
| Exame Demissional                 | occupational_health  | -     | 0 meses  |
| Exame de Retorno ao Trabalho     | occupational_health  | -     | 0 meses  |
| Exame de Mudanca de Funcao       | occupational_health  | -     | 0 meses  |
| NR-11 Empilhadeira/Transporte    | regulatory_norm      | NR-11 | 12 meses |
| NR-12 Seguranca em Maquinas      | regulatory_norm      | NR-12 | 12 meses |
| NR-35 Trabalho em Altura         | regulatory_norm      | NR-35 | 24 meses |
| NR-06 EPIs                        | regulatory_norm      | NR-06 | 12 meses |

### Treinamentos (Seeder)
| Nome                              | Categoria    | NR    | Validade |
|-----------------------------------|-------------|-------|----------|
| Operacao de Empilhadeira          | operational | NR-11 | 12 meses |
| Movimentacao Manual de Cargas     | operational | NR-11 | 12 meses |
| Seguranca do Trabalho Geral      | safety      | -     | 12 meses |
| Brigada de Incendio / CIPA       | safety      | NR-23 | 12 meses |
| Primeiros Socorros                | safety      | -     | 24 meses |
| Integracao de Novos Funcionarios  | onboarding  | -     | 0 meses  |
