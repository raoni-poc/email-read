<?php

namespace App\Modules\IMAP;

class HandleEmails
{
    private $imap;
    private $imapStream;

    public function __construct(IMAP $imap)
    {
        $this->imap = $imap;
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
            $overview = imap_fetch_overview($this->imapStream, $email_number, 0);
            $structure = imap_fetchstructure($this->imapStream, $email_number);
            $result[] = [
                'subject' => $overview[0]->subject,
                'from' => $overview[0]->from,
                'to' => $overview[0]->to,
                'date' => $overview[0]->date,
                'uid' => $overview[0]->uid,
                'body' => $this->getBody($structure, $email_number),
//                'attachment' => $this->getAttachment($structure, $email_number),
            ];
        }
        return $result;
    }

    private function getBody(object $structure, $email_number)
    {
        if (!isset($structure->parts) && !count($structure->parts)) {
            return null;
        }
        $text = '';
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
            if (!empty($text)) {
                break;
            }
            die($text);

        }
        return $text;
    }

    private function getAttachment(object $structure, $email_number)
    {
        $attachments = [];
        if (!isset($structure->parts) && !count($structure->parts)) {
            return $attachments;
        }
        for ($i = 0; $i < count($structure->parts); $i++) {
            if ($structure->parts[$i]->ifdparameters) {
                foreach ($structure->parts[$i]->dparameters as $object) {
                    if (strtolower($object->attribute) == 'filename') {
                        $attachments[$i]['filename'] = $object->value;
                    }
                }
            }

            if ($structure->parts[$i]->ifparameters) {
                foreach ($structure->parts[$i]->parameters as $object) {
                    if (strtolower($object->attribute) == 'name') {
                        $attachments[$i]['name'] = $object->value;
                    }
                }
            }

            if (isset($attachments[$i])) {
                $attachments[$i]['attachment'] = imap_fetchbody($this->imapStream, $email_number, $i + 1);
                /* 3 = BASE64 encoding */
                if ($structure->parts[$i]->encoding == 3) {
                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                } /* 4 = QUOTED-PRINTABLE encoding */
                elseif ($structure->parts[$i]->encoding == 4) {
                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                }
            }
        }
        return $attachments;
    }

    private function isAttachment($structurePart)
    {
        if ($structurePart->ifdparameters) {
            foreach ($structurePart->dparameters as $object) {
                if (strtolower($object->attribute) == 'filename') {
                    return 'filename';
                }
            }
        }

        if ($structurePart->ifparameters) {
            foreach ($structurePart->parameters as $object) {
                if (strtolower($object->attribute) == 'name') {
                    return 'name';
                }
            }
        }
        return false;
    }

    private function resultDecode(string $text, $encoding)
    {
        switch ($encoding) {
            # 7BIT
            case 0:
                return utf8_decode($text);
            # 8BIT
            case 1:
                return quoted_printable_decode(imap_8bit($text));
            # BINARY
            case 2:
                return imap_binary($text);
            # BASE64
            case 3:
                return imap_base64($text);
            # QUOTED-PRINTABLE
            case 4:
                return quoted_printable_decode($text);
            # UNKNOWN
            default:
                return $text;
        }
    }

}
