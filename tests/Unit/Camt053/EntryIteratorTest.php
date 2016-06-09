<?php

namespace Genkgo\Camt\Unit\Camt053;

use Genkgo\Camt\AbstractTestCase;
use Genkgo\Camt\Camt053\Decoder;
use Genkgo\Camt\Camt053\MessageFormat;
use Genkgo\Camt\Camt053\DTO;

class EntryIteratorTest extends AbstractTestCase
{
    protected function getDefaultMessage()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(__DIR__.'/Stubs/camt053.multi.statement.xml');
        return (new MessageFormat\Camt053V02)->getDecoder()->decode($dom);
    }

    public function testMultipleStatements()
    {
        $message = $this->getDefaultMessage();
        $entries = $message->getEntries();

        $item = 0;
        foreach ($entries as $entry) {
            /* @var DTO\Entry $entry */
            if ($item === 0) {
                $this->assertEquals(885, $entry->getAmount()->getAmount());
                $this->assertEquals(
                    'Transaction Description 1',
                    $entry->getTransactionDetail()->getRemittanceInformation()->getMessage()
                );
                $this->assertEquals(
                    'Company Name 1',
                    $entry->getTransactionDetail()->getRelatedParty()->getRelatedPartyType()->getName()
                );
                $this->assertEquals(
                   'NL',
                   $entry->getTransactionDetail()->getRelatedParty()->getRelatedPartyType()->getAddress()->getCountry()
                );
                $this->assertEquals(
                    '000000001',
                    $entry->getTransactionDetail()->getReference()->getEndToEndId()
                );
            }

            if ($item === 1) {
                $this->assertEquals(-700, $entry->getAmount()->getAmount());
                $this->assertEquals(
                    'Transaction Description 2',
                    $entry->getTransactionDetail()->getRemittanceInformation()->getMessage()
                );
                $this->assertEquals(
                    'Company Name 2',
                    $entry->getTransactionDetail()->getRelatedParty()->getRelatedPartyType()->getName()
                );
                $this->assertEquals(
                   'FR',
                   $entry->getTransactionDetail()->getRelatedParty()->getRelatedPartyType()->getAddress()->getCountry()
                );
                $this->assertEquals(
                    '000000002',
                    $entry->getTransactionDetail()->getReference()->getEndToEndId()
                );
            }

            $item++;
        }
    }
}
