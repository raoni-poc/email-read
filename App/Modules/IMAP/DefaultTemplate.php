<?php


namespace App\Modules\IMAP;


class DefaultTemplate
{
    private $decoder;

    public function __construct(DecodeTamplate $decoder)
    {
        $this->decoder = $decoder;
    }

    public function getInvoiceName($body)
    {
        return $this->getTemplateItem($body, 'nome:');
    }

    public function getInvoiceAddress($body)
    {
        return $this->getTemplateItem($body, 'endereço:');
    }

    public function getInvoiceAmount($body)
    {
        return $this->getTemplateItem($body, 'valor:');
    }

    public function getInvoiceMonthPayment($body)
    {
        return $this->getTemplateItem($body, 'vencimento:');
    }

    public function hasAttachment($structure)
    {
        foreach ($structure->parts as $part) {
            if (isset($part->disposition) && $part->disposition == 'ATTACHMENT') {
                return true;
            }
        }
    }

    public function isValidBody(string $body)
    {
        $strOpen = 'segue meus dados de contato e informações para pagamento';
        $strName = 'nome:';
        $strAddress = 'endereço:';
        $strValor = 'valor:';
        $strMonthPayment = 'vencimento:';

        $newBody = $this->breakBodyRowsInArray($body);

        //Calcula a similariadade
        foreach ($newBody as $row) {
            $mathOpen = 0;
            similar_text($strOpen, $row, $percent);
            if ($percent < 50) {
                continue;
            } else {
                $mathOpen++;
                break;
            }
        }
        if (!$mathOpen) {
            return false;
        }
        $nameValid = is_int(strpos(mb_strtolower($body), $strName));
        $addressValid = is_int(strpos(mb_strtolower($body), $strAddress));
        $amountValid = is_int(strpos(mb_strtolower($body), $strValor));
        $monthPaymentValid = is_int(strpos(mb_strtolower($body), $strMonthPayment));

        return ($nameValid && $addressValid && $amountValid && $monthPaymentValid);
    }

    public function saveInDiskAndGetUrlToAttachment(object $structure, $email_number, $uid, $pathToSave, $imapStream, $host)
    {
        $attachments = [];
        for ($i = 0; $i < count($structure->parts); $i++) {
            $fileName = null;
            $file = null;
            if ($structure->parts[$i]->ifdparameters) {
                foreach ($structure->parts[$i]->dparameters as $object) {
                    if (strtolower($object->attribute) == 'filename') {
                        $fileName = $object->value;
                    }
                }
            }
            if (isset($fileName)) {
                $file = imap_fetchbody($imapStream, $email_number, $i + 1);
                $file = $this->decoder->decode($file, $structure->parts[$i]->encoding);
                $filePath = $pathToSave . '/' . $uid . '_' . $fileName;
                file_put_contents($filePath, $file);
                $attachments[] = $host . (str_replace('public/', '', $filePath));
            }
        }
        return $attachments;
    }

    public function getBody(object $structure, $email_number, $imapStream)
    {
        $text = '';
        $encoding = null;
        for ($i = 0; $i < count($structure->parts); $i++) {
            if ($this->isAttachment($structure->parts[$i])) {
                continue;
            }
            $text = imap_fetchbody($imapStream, $email_number, 1.1);
            if (empty($text)) {
                $text = imap_fetchbody($imapStream, $email_number, 2);
            }
            if (empty($text)) {
                $text = imap_fetchbody($imapStream, $email_number, $i + 1);
            }
            $encoding = $structure->parts[$i]->encoding;
            if (!empty($text)) {
                break;
            }
        }
        return trim($this->decoder->decode($text, $encoding));
    }

    private function breakBodyRowsInArray($body)
    {
        $newBody = [];
        $bodyArr = explode(PHP_EOL, $body);
        foreach ($bodyArr as $row) {
            if (!empty(trim($row))) {
                $newBody[] = str_replace(' : ', ': ', mb_strtolower(trim($row)));
            }
        }
        return $newBody;
    }

    private function getTemplateItem($body, $item)
    {
        $bodyArr = $this->breakBodyRowsInArray($body);
        foreach ($bodyArr as $row) {
            if (is_int(strpos($row, $item))) {
                return trim(explode($item, $row)[1]);
            }
        }
    }

    private function isAttachment($structurePart)
    {
        if (isset($structurePart->disposition) && $structurePart->disposition == 'ATTACHMENT') {
            return true;
        }
        return false;
    }
}
