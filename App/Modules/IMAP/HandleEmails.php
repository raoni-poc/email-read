<?php

namespace App\Modules\IMAP;

use App\Config;

class HandleEmails
{
    private $imap;
    private $imapStream;
    private $pathToSaveAttachment;
    private $host;
    private $tamplateTools;

    public function __construct(IMAP $imap, DefaultTemplate $templateTools, Config $config)
    {
        $this->imap = $imap;
        $this->pathToSaveAttachment = $config->getPathToSaveAttachment();
        $this->host = $config->getHost();
        $this->tamplateTools = $templateTools;
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
            if (!$this->tamplateTools->hasAttachment($structure) ||
                !isset($structure->parts) ||
                !count($structure->parts)) {
                continue;
            }
            $body = $this->tamplateTools->getBody($structure, $email_number, $this->imapStream);
            if (!$this->tamplateTools->isValidBody($body)) {
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
                'attachment' => $this->tamplateTools->saveInDiskAndGetUrlToAttachment($structure,
                    $email_number,
                    $overview[0]->uid,
                    $this->pathToSaveAttachment,
                    $this->imapStream,
                    $this->host),
                'invoice_info' => [
                    'name' => $this->tamplateTools->getInvoiceName($body),
                    'address' => $this->tamplateTools->getInvoiceAddress($body),
                    'amount' => $this->tamplateTools->getInvoiceAmount($body),
                    'month_payment' => $this->tamplateTools->getInvoiceMonthPayment($body),
                ]
            ];
        }
        return $result;
    }
}
