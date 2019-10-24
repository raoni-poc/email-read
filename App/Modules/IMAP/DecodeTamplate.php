<?php


namespace App\Modules\IMAP;


class DecodeTamplate
{
    public function decode($data, $encoding)
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
}
