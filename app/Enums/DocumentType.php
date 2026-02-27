<?php

namespace App\Enums;

enum DocumentType: string
{
    case Rg = 'rg';
    case Cpf = 'cpf';
    case Cnh = 'cnh';
    case Ctps = 'ctps';
    case ProofOfResidence = 'proof_of_residence';
    case MedicalCertificate = 'medical_certificate';
    case WorkContract = 'work_contract';
    case TerminationDocument = 'termination_document';
    case Photo = 'photo';
    case VaccinationCard = 'vaccination_card';
    case Pis = 'pis';
    case ReservistCertificate = 'reservist_certificate';
    case VoterRegistration = 'voter_registration';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Rg                  => 'RG',
            self::Cpf                 => 'CPF',
            self::Cnh                 => 'CNH',
            self::Ctps                => 'CTPS',
            self::ProofOfResidence    => 'Comprovante de Residência',
            self::MedicalCertificate  => 'Atestado / Laudo Médico',
            self::WorkContract        => 'Contrato de Trabalho',
            self::TerminationDocument => 'Documentos de Rescisão',
            self::Photo               => 'Foto',
            self::VaccinationCard     => 'Cartão de Vacinação',
            self::Pis                 => 'PIS/PASEP',
            self::ReservistCertificate => 'Certificado de Reservista',
            self::VoterRegistration   => 'Título de Eleitor',
            self::Other               => 'Outros',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Rg, self::Cpf, self::Pis => 'info',
            self::Cnh                      => 'warning',
            self::Ctps, self::WorkContract => 'success',
            self::MedicalCertificate       => 'danger',
            self::TerminationDocument      => 'gray',
            default                        => 'primary',
        };
    }
}
