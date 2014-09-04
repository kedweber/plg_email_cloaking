<?php

class PlgSystemCloak extends JPlugin
{
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        $this->params->atrr_pre = 'data-ep-a';
        $this->params->atrr_post = 'data-ep-b';

        // email@domain.com
        $this->params->regex_email = '([\w\.\-]+\@(?:[a-z0-9\.\-]+\.)+(?:[a-z0-9\-]{2,10}))';

        $this->params->regex = '#' . $this->params->regex_email . '#i';
        $this->params->regex_js = '#<script[^>]*[^/]>.*?</script>#si';
        $this->params->regex_injs = '#([\'"])' . $this->params->regex_email . '\1#i';
        $this->params->regex_link = '#<a\s+((?:[^>]*\s+)?)href\s*=\s*"mailto:(' . $this->params->regex_email . '(?:\?[^"]+)?)"((?:\s+[^>]*)?)>(.*?)</a>#si';
    }

    public function onAfterDispatch() {
        if(JFactory::getApplication()->isSite())
        {
            $script = 'var emailProtector=emailProtector||{};emailProtector.addCloakedMailto=function(f,g){var a=document.getElementById(f);if(a){for(var e=a.getElementsByTagName("span"),b="",c="",d=0,h=e.length;d<h;d++)b+=e[d].getAttribute("' . $this->params->atrr_pre . '"),c=e[d].getAttribute("' . $this->params->atrr_post . '")+c;a.innerHTML=b+c;g&&(a.parentNode.href="mailto:"+b+c)}};';
            JFactory::getDocument()->addScriptDeclaration('/* START: ' . $this->name . ' scripts */ ' . preg_replace('#\n\s*#s', ' ', trim($script)) . ' /* END: ' . $this->name . ' scripts */');
        }
    }

	public function onAfterRender()
	{
		$application = JFactory::getApplication();
        $format = KRequest::format();

        // If the format is NULL, nooku defaults back to html, so there is a separate check for html and raw.
        if(is_null($format) || $format == 'html' || $format == 'raw') {
            if($application->isSite())
            {
                $body = JResponse::getBody();

                while (preg_match($this->params->regex_link, $body, $regs, PREG_OFFSET_CAPTURE))
                {
                    $mail = str_replace('&amp;', '&', $regs[2][0]);
                    $protected = $this->_protectEmail($mail, $regs[5][0], $regs[1][0], $regs[4][0]);
                    $body = substr_replace($body, $protected, $regs[0][1], strlen($regs[0][0]));
                }

                while (preg_match($this->params->regex, $body, $regs, PREG_OFFSET_CAPTURE))
                {
                    $protected = $this->_protectEmail('', $regs[1][0]);
                    $body = substr_replace($body, $protected, $regs[1][1], strlen($regs[1][0]));
                }

                JResponse::setBody($body);
            }
        }
	}

    private function _protectEmail($mailto, $text = '', $pre = '', $post = '')
    {
        $id = 'ep_' . substr(md5(rand()), 0, 8);

        if($mailto) {
            $text = $this->_createSpans($mailto, $id);
            return $this->createLink($text, $id, $pre, $post);
        }

        if($text) {
            while (preg_match($this->params->regex, $text, $regs, PREG_OFFSET_CAPTURE))
            {
                $protected = $this->_createSpans($regs[1][0], $id);
                $text = substr_replace($text, $protected, $regs[1][1], strlen($regs[1][0]));
            }
        }

        if ($id)
        {
            return self::_createOutput($text, $id);
        }

        return $text;
    }

    private function _createSpans($str, $id = 0, $hide = 0) {
        $split = str_split($str);
        $size = ceil(count($split) / 6);
        $parts = array('', '', '', '', '', '');
        foreach ($split as $i => $c)
        {
            $v = ($c == '@' || (strlen($c) === 1 && rand(0, 2))) ? '&#' . ord($c) . ';' : $c;
            $pos = floor($i / $size);
            $parts[$pos] .= $v;
        }

        $parts = array(
            array($parts['0'], $parts['5']),
            array($parts['1'], $parts['4']),
            array($parts['2'], $parts['3'])
        );

        $html = array();

        $html[] = '<span class="cloaked_email"' . ($id ? ' id="' . $id . '"' : '') . '' . ($hide ? ' style="display:none;"' : '') . '>';
        foreach ($parts as $part)
        {
            $atrr = array(
                $this->params->atrr_pre . '="' . $part['0'] . '"',
                $this->params->atrr_post . '="' . $part['1'] . '"'
            );
            shuffle($atrr);
            $html[] = '<span ' . implode(' ', $atrr) . '>';
        }
        $html[] = '</span></span></span></span>';

        return implode('', $html);
    }

    protected function createLink($text, $id, $pre = '', $post = '')
    {
        return
            '<a ' . $pre . 'href="javascript:// ' . htmlentities(JText::_('EP_MESSAGE_PROTECTED'), ENT_COMPAT, 'UTF-8') . '"' . $post . '>'
            . $text
            . '</a>'
            . '<script type="text/javascript">emailProtector.addCloakedMailto("' . $id . '", 1);</script>';
    }

    protected function _createOutput($text, $id)
    {
        return '<!--- ' . JText::_('EP_MESSAGE_PROTECTED') . ' --->' . $text
        . '<script type="text/javascript">emailProtector.addCloakedMailto("' . $id . '", 0);</script>';
    }
}