<?php

class Mail extends MailCore
{
    public static function Send($id_lang, $template, $subject, $template_vars, $to,
        $to_name = null, $from = null, $from_name = null, $file_attachment = null, $mode_smtp = null,
        $template_path = _PS_MAIL_DIR_, $die = false, $id_shop = null, $bcc = null)
    {
        if (!$id_shop)
            $id_shop = Context::getContext()->shop->id;
    
        $configuration = Configuration::getMultiple(array(
            'PS_SHOP_EMAIL',
            'PS_MAIL_METHOD',
            'PS_MAIL_SERVER',
            'PS_MAIL_USER',
            'PS_MAIL_PASSWD',
            'PS_SHOP_NAME',
            'PS_MAIL_SMTP_ENCRYPTION',
            'PS_MAIL_SMTP_PORT',
            'PS_MAIL_TYPE'
        ), null, null, $id_shop);
    
        // Returns immediatly if emails are deactivated
        if ($configuration['PS_MAIL_METHOD'] == 3)
            return true;
    
        $theme_path = _PS_THEME_DIR_;
    
        // Get the path of theme by id_shop if exist
        if (is_numeric($id_shop) && $id_shop)
        {
            $shop = new Shop((int)$id_shop);
            $theme_name = $shop->getTheme();
    
            if (_THEME_NAME_ != $theme_name)
                $theme_path = _PS_ROOT_DIR_.'/themes/'.$theme_name.'/';
        }
    
        if (!isset($configuration['PS_MAIL_SMTP_ENCRYPTION']))
            $configuration['PS_MAIL_SMTP_ENCRYPTION'] = 'off';
        if (!isset($configuration['PS_MAIL_SMTP_PORT']))
            $configuration['PS_MAIL_SMTP_PORT'] = 'default';
    
        // Sending an e-mail can be of vital importance for the merchant, when his password is lost for example, so we must not die but do our best to send the e-mail
        if (!isset($from) || !Validate::isEmail($from))
            $from = $configuration['PS_SHOP_EMAIL'];
        if (!Validate::isEmail($from))
            $from = null;
    
        // $from_name is not that important, no need to die if it is not valid
        if (!isset($from_name) || !Validate::isMailName($from_name))
            $from_name = $configuration['PS_SHOP_NAME'];
        if (!Validate::isMailName($from_name))
            $from_name = null;
    
        // It would be difficult to send an e-mail if the e-mail is not valid, so this time we can die if there is a problem
        if (!is_array($to) && !Validate::isEmail($to))
        {
            Tools::dieOrLog(Tools::displayError('Error: parameter "to" is corrupted'), $die);
            return false;
        }
    
        // if bcc is not null, make sure it's a vaild e-mail
        if (!is_null($bcc) && !is_array($bcc) && !Validate::isEmail($bcc))
        {
            Tools::dieOrLog(Tools::displayError('Error: parameter "bcc" is corrupted'), $die);
            $bcc = null;
        }
    
        if (!is_array($template_vars))
            $template_vars = array();
    
        // Do not crash for this error, that may be a complicated customer name
        if (is_string($to_name) && !empty($to_name) && !Validate::isMailName($to_name))
            $to_name = null;
    
        if (!Validate::isTplName($template))
        {
            Tools::dieOrLog(Tools::displayError('Error: invalid e-mail template'), $die);
            return false;
        }
    
        if (!Validate::isMailSubject($subject))
        {
            Tools::dieOrLog(Tools::displayError('Error: invalid e-mail subject'), $die);
            return false;
        }
    
        /* Construct multiple recipients list if needed */
        $to_list = new Swift_RecipientList();
        if (is_array($to) && isset($to))
        {
            foreach ($to as $key => $addr)
            {
                $addr = trim($addr);
                if (!Validate::isEmail($addr))
                {
                    Tools::dieOrLog(Tools::displayError('Error: invalid e-mail address'), $die);
                    return false;
                }
    
                if (is_array($to_name) && $to_name && is_array($to_name) && Validate::isGenericName($to_name[$key]))
                    $to_name = $to_name[$key];
    
                $to_name = (($to_name == null || $to_name == $addr) ? '' : self::mimeEncode($to_name));
                $to_list->addTo($addr, $to_name);
            }
            $to_plugin = $to[0];
        }
        else
        {
            /* Simple recipient, one address */
            $to_plugin = $to;
            $to_name = (($to_name == null || $to_name == $to) ? '' : self::mimeEncode($to_name));
            $to_list->addTo($to, $to_name);
        }
        if (isset($bcc))
            $to_list->addBcc($bcc);
    
        try {
            /* Connect with the appropriate configuration */
            if ($configuration['PS_MAIL_METHOD'] == 2)
            {
                if (empty($configuration['PS_MAIL_SERVER']) || empty($configuration['PS_MAIL_SMTP_PORT']))
                {
                    Tools::dieOrLog(Tools::displayError('Error: invalid SMTP server or SMTP port'), $die);
                    return false;
                }
                $connection = new Swift_Connection_SMTP(
                    $configuration['PS_MAIL_SERVER'],
                    $configuration['PS_MAIL_SMTP_PORT'],
                    $configuration['PS_MAIL_SMTP_ENCRYPTION'] == 'ssl' ? Swift_Connection_SMTP::ENC_SSL : (($configuration['PS_MAIL_SMTP_ENCRYPTION'] == 'tls' ? Swift_Connection_SMTP::ENC_TLS : Swift_Connection_SMTP::ENC_OFF))
                );
                $connection->setTimeout(15);
                if (!$connection)
                    return false;
                if (!empty($configuration['PS_MAIL_USER']))
                    $connection->setUsername($configuration['PS_MAIL_USER']);
                if (!empty($configuration['PS_MAIL_PASSWD']))
                    $connection->setPassword($configuration['PS_MAIL_PASSWD']);
            }
            else
                $connection = new Swift_Connection_NativeMail();
    
            if (!$connection)
                return false;
            $swift = new Swift($connection, Configuration::get('PS_MAIL_DOMAIN', null, null, $id_shop));
            /* Get templates content */
            $iso = Language::getIsoById((int)$id_lang);
            if (!$iso)
            {
                Tools::dieOrLog(Tools::displayError('Error - No ISO code for email'), $die);
                return false;
            }
            $iso_template = $iso.'/'.$template;
    
            $module_name = false;
            $override_mail = false;
    
            // get templatePath
            if (preg_match('#'.$shop->physical_uri.'modules/#', str_replace(DIRECTORY_SEPARATOR, '/', $template_path)) && preg_match('#modules/([a-z0-9_-]+)/#ui', str_replace(DIRECTORY_SEPARATOR, '/',$template_path), $res))
                $module_name = $res[1];
    
            if ($module_name !== false && (file_exists($theme_path.'modules/'.$module_name.'/mails/'.$iso_template.'.txt') ||
                file_exists($theme_path.'modules/'.$module_name.'/mails/'.$iso_template.'.html')))
                $template_path = $theme_path.'modules/'.$module_name.'/mails/';
            elseif (file_exists($theme_path.'mails/'.$iso_template.'.txt') || file_exists($theme_path.'mails/'.$iso_template.'.html'))
            {
                $template_path = $theme_path.'mails/';
                $override_mail  = true;
            }
            if (!file_exists($template_path.$iso_template.'.txt') && ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_TEXT))
            {
                Tools::dieOrLog(Tools::displayError('Error - The following e-mail template is missing:').' '.$template_path.$iso_template.'.txt', $die);
                return false;
            }
            elseif (!file_exists($template_path.$iso_template.'.html') && ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_HTML))
            {
                Tools::dieOrLog(Tools::displayError('Error - The following e-mail template is missing:').' '.$template_path.$iso_template.'.html', $die);
                return false;
            }
            $template_html = file_get_contents($template_path.$iso_template.'.html');
            $template_txt = strip_tags(html_entity_decode(file_get_contents($template_path.$iso_template.'.txt'), null, 'utf-8'));
    
            if ($override_mail && file_exists($template_path.$iso.'/lang.php'))
                    include_once($template_path.$iso.'/lang.php');
            elseif ($module_name && file_exists($theme_path.'mails/'.$iso.'/lang.php'))
                include_once($theme_path.'mails/'.$iso.'/lang.php');
            elseif (file_exists(_PS_MAIL_DIR_.$iso.'/lang.php'))
                include_once(_PS_MAIL_DIR_.$iso.'/lang.php');
            else
            {
                Tools::dieOrLog(Tools::displayError('Error - The language file is missing for:').' '.$iso, $die);
                return false;
            }
    
            /* Create mail and attach differents parts */
            $subject = '['.Configuration::get('PS_SHOP_NAME', null, null, $id_shop).'] '.$subject;
            $message = new Swift_Message($subject);
    
            $message->setCharset('utf-8');
    
            /* Set Message-ID - getmypid() is blocked on some hosting */
            $message->setId(Mail::generateId());
    
            $message->headers->setEncoding('Q');
    
            $template_vars = array_map(array('Tools', 'htmlentitiesDecodeUTF8'), $template_vars);
            $template_vars = array_map(array('Tools', 'stripslashes'), $template_vars);
    
            if (Configuration::get('PS_LOGO_MAIL') !== false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_MAIL', null, null, $id_shop)))
                $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO_MAIL', null, null, $id_shop);
            else
            {
                if (file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $id_shop)))
                    $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO', null, null, $id_shop);
                else
                    $template_vars['{shop_logo}'] = '';
            }
            ShopUrl::cacheMainDomainForShop((int)$id_shop);
            /* don't attach the logo as */
            if (isset($logo))
                $template_vars['{shop_logo}'] = $message->attach(new Swift_Message_EmbeddedFile(new Swift_File($logo), null, ImageManager::getMimeTypeByExtension($logo)));
    
            if ((Context::getContext()->link instanceof Link) === false)
                Context::getContext()->link = new Link();
    
            $template_vars['{shop_name}'] = Tools::safeOutput(Configuration::get('PS_SHOP_NAME', null, null, $id_shop));
            $template_vars['{shop_url}'] = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, null, false, $id_shop);
            $template_vars['{my_account_url}'] = Context::getContext()->link->getPageLink('my-account', true, Context::getContext()->language->id, null, false, $id_shop);
            $template_vars['{guest_tracking_url}'] = Context::getContext()->link->getPageLink('guest-tracking', true, Context::getContext()->language->id, null, false, $id_shop);
            $template_vars['{history_url}'] = Context::getContext()->link->getPageLink('history', true, Context::getContext()->language->id, null, false, $id_shop);
            $template_vars['{color}'] = Tools::safeOutput(Configuration::get('PS_MAIL_COLOR', null, null, $id_shop));
            $swift->attachPlugin(new Swift_Plugin_Decorator(array($to_plugin => $template_vars)), 'decorator');
            if ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_TEXT)
                $message->attach(new Swift_Message_Part($template_txt, 'text/plain', '8bit', 'utf-8'));
            if ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_HTML)
                $message->attach(new Swift_Message_Part($template_html, 'text/html', '8bit', 'utf-8'));
            if ($file_attachment && !empty($file_attachment))
            {
                // Multiple attachments?
                if (!is_array(current($file_attachment)))
                    $file_attachment = array($file_attachment);
    
                foreach ($file_attachment as $attachment)
                    if (isset($attachment['content']) && isset($attachment['name']) && isset($attachment['mime']))
                        $message->attach(new Swift_Message_Attachment($attachment['content'], $attachment['name'], $attachment['mime']));
            }
            /* Send mail */
            $send = $swift->send($message, $to_list, new Swift_Address($from, $from_name));
            $swift->disconnect();
    
            ShopUrl::resetMainDomainCache();
    
            if ($send && Configuration::get('PS_LOG_EMAILS'))
            {
                $mail = new Mail();
                $mail->template = substr($template, 0, 62);
                $mail->subject = substr($subject, 0, 254);
                $mail->id_lang = (int)$id_lang;
                foreach (array_merge($to_list->getTo(), $to_list->getCc(), $to_list->getBcc()) as $recipient)
                {
                    $mail->id = null;
                    $mail->recipient = substr($recipient->getAddress(), 0, 126);
                    $mail->add();
                }
            }
    
            return $send;
        }
        catch (Swift_Exception $e) {
            return false;
        }
    }
    
    public static function eraseAllLogs()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE '._DB_PREFIX_.'mail');
    }
    
    public static function sendMailTest($smtpChecked, $smtpServer, $content, $subject, $type, $to, $from, $smtpLogin, $smtpPassword, $smtpPort = 25, $smtpEncryption)
    {
        $result = false;
        try {
            if ($smtpChecked)
            {
                $smtp = new Swift_Connection_SMTP($smtpServer, $smtpPort, ($smtpEncryption == 'off') ?
                    Swift_Connection_SMTP::ENC_OFF : (($smtpEncryption == 'tls') ? Swift_Connection_SMTP::ENC_TLS : Swift_Connection_SMTP::ENC_SSL));
                $smtp->setUsername($smtpLogin);
                $smtp->setpassword($smtpPassword);
                $smtp->setTimeout(15);
                $swift = new Swift($smtp, Configuration::get('PS_MAIL_DOMAIN'));
            }
            else
                $swift = new Swift(new Swift_Connection_NativeMail(), Configuration::get('PS_MAIL_DOMAIN'));
    
            $message = new Swift_Message($subject, $content, $type);
    
            if ($swift->send($message, $to, $from))
                $result = true;
    
            $swift->disconnect();
        } catch (Swift_ConnectionException $e) {
            $result = $e->getMessage();
        } catch (Swift_Message_MimeException $e) {
            $result = $e->getMessage();
        }
    
        return $result;
    }
}