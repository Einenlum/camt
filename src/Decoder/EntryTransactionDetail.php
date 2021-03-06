<?php

namespace Genkgo\Camt\Decoder;

use Genkgo\Camt\DTO;
use \SimpleXMLElement;
use Genkgo\Camt\Iban;

abstract class EntryTransactionDetail
{
    /**
     * @param DTO\EntryTransactionDetail $detail
     * @param SimpleXMLElement           $xmlDetail
     */
    public function addReferences(DTO\EntryTransactionDetail $detail, SimpleXMLElement $xmlDetail)
    {
        if (false === isset($xmlDetail->Refs)) {
            return;
        }

        $refs = $xmlDetail->Refs;
        $reference = new DTO\Reference();

        $reference->setMessageId(isset($refs->MsgId) ? (string) $refs->MsgId : null);
        $reference->setAccountServiceReference(isset($refs->AcctSvcrRef) ? (string) $refs->AcctSvcrRef : null);
        $reference->setPaymentInformationId(isset($refs->PmtInfId) ? (string) $refs->PmtInfId : null);
        $reference->setInstructionId(isset($refs->InstrId) ? (string) $refs->InstrId : null);
        $reference->setEndToEndId(isset($refs->EndToEndId) ? (string) $refs->EndToEndId : null);
        $reference->setTransactionId(isset($refs->TxId) ? (string) $refs->TxId : null);
        $reference->setMandateId(isset($refs->MndtId) ? (string) $refs->MndtId : null);
        $reference->setChequeNumber(isset($refs->ChqNb) ? (string) $refs->ChqNb : null);
        $reference->setClearingSystemReference(isset($refs->ClrSysRef) ? (string) $refs->ClrSysRef : null);
        $reference->setAccountOwnerTransactionId(isset($refs->AcctOwnrTxId) ? (string) $refs->AcctOwnrTxId : null);
        $reference->setAccountServicerTransactionId(isset($refs->AcctSvcrTxId) ? (string) $refs->AcctSvcrTxId : null);
        $reference->setMarketInfrastructureTransactionId(isset($refs->MktInfrstrctrTxId) ? (string) $refs->MktInfrstrctrTxId : null);
        $reference->setProcessingId(isset($refs->PrcgId) ? (string) $refs->PrcgId : null);

        foreach ($refs->Prtry as $xmlProprietary) {
            $type = isset($xmlProprietary->Tp) ? (string) $xmlProprietary->Tp : null;
            $subReference = isset($xmlProprietary->Ref) ? (string) $xmlProprietary->Ref : null;

            $reference->addProprietary(new DTO\ProprietaryReference($type, $subReference));
        }

        $detail->addReference($reference);
    }

    /**
     * @param DTO\EntryTransactionDetail $detail
     * @param SimpleXMLElement           $xmlDetail
     */
    public function addRelatedParties(DTO\EntryTransactionDetail $detail, SimpleXMLElement $xmlDetail)
    {
        if (false === isset($xmlDetail->RltdPties)) {
            return;
        }

        foreach ($xmlDetail->RltdPties as $xmlRelatedParty) {
            if (isset($xmlRelatedParty->Cdtr)) {
                $xmlRelatedPartyType = $xmlRelatedParty->Cdtr;
                $xmlRelatedPartyTypeAccount = $xmlRelatedParty->CdtrAcct;
                $relatedPartyType = $creditor = new DTO\Creditor((string) $xmlRelatedPartyType->Nm);
            } elseif (isset($xmlRelatedParty->Dbtr)) {
                $xmlRelatedPartyType = $xmlRelatedParty->Dbtr;
                $xmlRelatedPartyTypeAccount = $xmlRelatedParty->DbtrAcct;
                $relatedPartyType = $creditor = new DTO\Debtor((string) $xmlRelatedPartyType->Nm);
            } else {
                continue;
            }

            if (isset($xmlRelatedPartyType->PstlAdr)) {
                $address = new DTO\Address();
                if (isset($xmlRelatedPartyType->PstlAdr->Ctry)) {
                    $address = $address->setCountry((string) $xmlRelatedPartyType->PstlAdr->Ctry);
                }
                if (isset($xmlRelatedPartyType->PstlAdr->CtrySubDvsn)) {
                    $address = $address->setCountrySubDivision((string) $xmlRelatedPartyType->PstlAdr->CtrySubDvsn);
                }
                if (isset($xmlRelatedPartyType->PstlAdr->Dept)) {
                    $address = $address->setDepartment((string) $xmlRelatedPartyType->PstlAdr->Dept);
                }
                if (isset($xmlRelatedPartyType->PstlAdr->SubDept)) {
                    $address = $address->setSubDepartment((string) $xmlRelatedPartyType->PstlAdr->SubDept);
                }
                if (isset($xmlRelatedPartyType->PstlAdr->StrtNm)) {
                    $address = $address->setStreetName((string) $xmlRelatedPartyType->PstlAdr->StrtNm);
                }
                if (isset($xmlRelatedPartyType->PstlAdr->BldgNb)) {
                    $address = $address->setBuildingNumber((string) $xmlRelatedPartyType->PstlAdr->BldgNb);
                }
                if (isset($xmlRelatedPartyType->PstlAdr->PstCd)) {
                    $address = $address->setPostCode((string) $xmlRelatedPartyType->PstlAdr->PstCd);
                }
                if (isset($xmlRelatedPartyType->PstlAdr->TwnNm)) {
                    $address = $address->setTownName((string) $xmlRelatedPartyType->PstlAdr->TwnNm);
                }
                if (isset($xmlRelatedPartyType->PstlAdr->AdrLine)) {
                    foreach ($xmlRelatedPartyType->PstlAdr->AdrLine as $line) {
                        $address = $address->addAddressLine((string)$line);
                    }
                }

                $relatedPartyType->setAddress($address);
            }

            $relatedParty = new DTO\RelatedParty($relatedPartyType, $this->getRelatedPartyAccount($xmlRelatedPartyTypeAccount));
            $detail->addRelatedParty($relatedParty);
        }
    }

    /**
     * @param DTO\EntryTransactionDetail $detail
     * @param SimpleXMLElement           $xmlDetail
     */
    public function addRemittanceInformation(DTO\EntryTransactionDetail $detail, SimpleXMLElement $xmlDetail)
    {
        if (false === isset($xmlDetail->RmtInf)) {
            return;
        }

        if (isset($xmlDetail->RmtInf->Ustrd)) {
            $remittanceInformation = DTO\RemittanceInformation::fromUnstructured(
                (string)$xmlDetail->RmtInf->Ustrd
            );
            $detail->setRemittanceInformation($remittanceInformation);

            return;
        }
        
        if (isset($xmlDetail->RmtInf->Strd)
            && isset($xmlDetail->RmtInf->Strd->CdtrRefInf)
            && isset($xmlDetail->RmtInf->Strd->CdtrRefInf->Ref)
        ) {
            $creditorReferenceInformation = DTO\CreditorReferenceInformation::fromUnstructured(
                (string)$xmlDetail->RmtInf->Strd->CdtrRefInf->Ref
            );
            $remittanceInformation = new DTO\RemittanceInformation();
            $remittanceInformation->setCreditorReferenceInformation($creditorReferenceInformation);
            $detail->setRemittanceInformation($remittanceInformation);
        }
    }

    /**
     * @param DTO\EntryTransactionDetail $detail
     * @param SimpleXMLElement           $xmlDetail
     */
    public function addReturnInformation(DTO\EntryTransactionDetail $detail, SimpleXMLElement $xmlDetail)
    {
        if (isset($xmlDetail->RtrInf) && isset($xmlDetail->RtrInf->Rsn->Cd)) {
            $remittanceInformation = DTO\ReturnInformation::fromUnstructured(
                (string)$xmlDetail->RtrInf->Rsn->Cd,
                (string)$xmlDetail->RtrInf->AddtlInf
            );
            $detail->setReturnInformation($remittanceInformation);
        }
    }

    /**
     * @param DTO\EntryTransactionDetail $detail
     * @param SimpleXMLElement           $xmlDetail
     */
    public function addAdditionalTransactionInformation(DTO\EntryTransactionDetail $detail, SimpleXMLElement $xmlDetail)
    {
        if (isset($xmlDetail->AddtlTxInf)) {
            $additionalInformation = new DTO\AdditionalTransactionInformation(
                (string) $xmlDetail->AddtlTxInf
            );
            $detail->setAdditionalTransactionInformation($additionalInformation);
        }
    }

    /**
     * @param SimpleXMLElement $xmlDetail
     *
     * @return DTO\Account|null
     */
    abstract public function getRelatedPartyAccount(SimpleXMLElement $xmlRelatedPartyTypeAccount);
}
