<?php

namespace App\Modules\IMAP;

use App\Config;

class HandleEmails
{
    private $imap;
    private $imapStream;
    private $pathToSaveAttachment;
    private $host;

    public function __construct(IMAP $imap, Config $config)
    {
        $this->imap = $imap;
        $this->pathToSaveAttachment = $config->getPathToSaveAttachment();
        $this->host = $config->getHost();
    }

    public function getAllInvoiceEmails()
    {
        $this->imapStream = $this->imap->openConnection();
        if (is_resource($this->imapStream)) {
            $search = imap_search($this->imapStream, 'ALL');
            $emails = $this->fetch($search);
            $this->imap->closeConnection();
            return $emails;
        }
        throw new \Exception('There is no IMAP connection');
    }

    private function fetch($search)
    {
        $result = [];
        if (empty($search)) {
            return $result;
        }
        rsort($search);

        foreach ($search as $key => $email_number) {
            $body = null;
            $structure = imap_fetchstructure($this->imapStream, $email_number);
            if (!$this->hasAttachment($structure) ||
                !isset($structure->parts) ||
                !count($structure->parts)) {
                continue;
            }
            $body = $this->getBody($structure, $email_number);
            if (!$this->isValidBody($body)) {
                continue;
            }
            $overview = imap_fetch_overview($this->imapStream, $email_number, 0);
            $result[] = [
                'subject' => $overview[0]->subject,
                'from' => $overview[0]->from,
                'to' => $overview[0]->to,
                'date' => $overview[0]->date,
                'uid' => $overview[0]->uid,
                'body' => $body,
                'attachment' => $this->getAttachment($structure, $email_number, $overview[0]->uid),
                'invoice_info' => [
                    'name' => $this->getInvoiceName($body),
                    'address' => $this->getInvoiceAddress($body),
                    'amount' => $this->getInvoiceAmount($body),
                    'month_payment' => $this->getInvoiceMonthPayment($body),
                ]
            ];
        }
        return $result;
    }

    private function getBody(object $structure, $email_number)
    {
        $text = '';
        $encoding = null;
        for ($i = 0; $i < count($structure->parts); $i++) {
            if ($this->isAttachment($structure->parts[$i])) {
                continue;
            }
            $text = imap_fetchbody($this->imapStream, $email_number, 1.1);
            if (empty($text)) {
                $text = imap_fetchbody($this->imapStream, $email_number, 2);
            }
            if (empty($text)) {
                $text = imap_fetchbody($this->imapStream, $email_number, $i + 1);
            }
            $encoding = $structure->parts[$i]->encoding;
            if (!empty($text)) {
                break;
            }
        }
//       return $this->fixSpecialChars(trim($this->resultDecode($text, $encoding)));
       return trim($this->resultDecode($text, $encoding));
    }

    private function getAttachment(object $structure, $email_number, $uid)
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
                $file = imap_fetchbody($this->imapStream, $email_number, $i + 1);
                $file = $this->resultDecode($file, $structure->parts[$i]->encoding);
                $filePath = $this->pathToSaveAttachment . '/' . $uid . '_' . $fileName;
                file_put_contents($filePath, $file);
                $attachments[] = $this->host . (str_replace('public/', '', $filePath));
            }
        }
        return $attachments;
    }

    private function resultDecode($data, $encoding)
    {
        switch ($encoding) {
            # 7BIT
            case 0:
                return imap_qprint(utf8_decode($data));
            # 8BIT
            case 1:
                return quoted_printable_decode(imap_8bit($data));
            # BINARY
            case 2:
                return imap_binary($data);
            # BASE64
            case 3:
                return imap_base64($data);
            # QUOTED-PRINTABLE
            case 4:
                return quoted_printable_decode($data);
            # UNKNOWN
            default:
                return $data;
        }
    }

    private function isAttachment($structurePart)
    {
        if (isset($structurePart->disposition) && $structurePart->disposition == 'ATTACHMENT') {
            return true;
        }
        return false;
    }

    private function hasAttachment($structure)
    {
        foreach ($structure->parts as $part) {
            if (isset($part->disposition) && $part->disposition == 'ATTACHMENT') {
                return true;
            }
        }
    }

    private function isValidBody(string $body)
    {
        $isValid = true;

        $strOpen = 'segue meus dados de contato e informa=C3=A7=C3=B5es para pagamento';
        $strOpen2 = 'segue meus dados de contato e informações para pagamento';
        $strName = 'nome:';
        $strAddress = 'endere=C3=A7o:';
        $strAddress2 = 'endereço:';
        $strValor = 'valor:';
        $strMonthPayment = 'vencimento:';

        $newBody = $this->breakBodyRowsInArray($body);

        //Calcula a similariadade
        foreach ($newBody as $row) {
            $mathOpen = 0;
            similar_text($strOpen, $row, $percent);
            if ($percent < 50) {
                similar_text($strOpen2, $row, $percent);
            }
            if ($percent < 50) {
                continue;
            } else {
                $mathOpen++;
                break;
            }
            echo $percent;
        }
        if (!$mathOpen) {
            return false;
        }

        return !(strpos($body, $strName) &&
            (strpos($body, $strAddress) || strpos($body, $strAddress2)) &&
            strpos($body, $strValor) &&
            strpos($body, $strMonthPayment)
        );
    }

    private function getInvoiceName($body)
    {
        return $this->getTemplateItem($body, 'nome:');
    }

    private function getInvoiceAddress($body)
    {
        return $this->getTemplateItem($body, 'endereço:');
    }

    private function getInvoiceAmount($body)
    {
        return $this->getTemplateItem($body, 'valor:');
    }

    private function getInvoiceMonthPayment($body)
    {
        return $this->getTemplateItem($body, 'vencimento:');
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
}
