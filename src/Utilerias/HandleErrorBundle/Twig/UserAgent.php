<?php

namespace Utilerias\HandleErrorBundle\Twig;

use Utilerias\HandleErrorBundle\Model\UserAgentModel;
/**
 * Description of UserAgent
 *
 * @author Javier
 */
class UserAgent extends \Twig_Extension {

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('browser', array($this, 'browserDetectFilter')),
            new \Twig_SimpleFilter('browser_version', array($this, 'getBrowser')),
        );
    }

    public function browserDetectFilter($userAgent) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match("/Explorer/", $userAgent) || $this->getInternetExplorerVersion())
            return 6;
        else if (preg_match("/iPad/", $userAgent))
            return 1;
        else if (preg_match("/iPhone/", $userAgent) || preg_match("/iPod/", $userAgent))
            return 2;
        else if (preg_match("/Firefox/", $userAgent))
            return 3;
        else if (preg_match("/Chrome/", $userAgent))
            return 4;
        else if (preg_match("/Safari/", $userAgent))
            return 5;
        else if (preg_match("/Android/", $userAgent))
            return 7;
        return 0;
    }

    private function getInternetExplorerVersion() {

        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        preg_match("/MSIE ([0-9]{1,}[\.0-9]{0,})/", $userAgent, $matches, PREG_OFFSET_CAPTURE);

        if (isset($matches[0][0]) && $matches[0][0] != "")
            return $matches[0][0];
        else
            return 0;
    }
    
    public function getName() {
        return 'user_agent';
    }

    public function getBrowser() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
}

        // Next get the name of the useragent yes seperately and for good reason

        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Trident/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer 11';
            $ub = "Trident";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } else {
            $bname = 'Unknown';
            $ub = "Unknown";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
                ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = isset($matches['version'][0]) ? $matches['version'][0] : '';
            } else {
                $version = (isset($matches['version'][1])) ? $matches['version'][1] : '';
            }
        } else {
            $version = isset($matches['version'][0]) ? $matches['version'][0] : '';
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

}