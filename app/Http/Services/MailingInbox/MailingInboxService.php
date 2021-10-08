<?php
namespace App\Http\Services\MailingInbox;
use App\Http\Repositories\MailingInbox\MailingInboxRep;

class MailingInboxService
{

    public static function getCredentials($user_id)
    {
        return MailingInboxRep::getCredentials($user_id);
    }

    public static function setCredentials($colaborador_id, $password, $host, $port)
    {
        return MailingInboxRep::setCredentials($colaborador_id, $password, $host, $port);
    }

    public static function updateCredentials($colaborador_id, $password, $host, $port)
    {
        return MailingInboxRep::updateCredentials($colaborador_id, $password, $host, $port);
    }

    public static function unsetCredentials($colaborador_id)
    {
        return MailingInboxRep::unsetCredentials($colaborador_id);
    }
}
