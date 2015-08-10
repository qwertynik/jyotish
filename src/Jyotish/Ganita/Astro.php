<?php
/**
 * @link      http://github.com/kunjara/jyotish for the canonical source repository
 * @license   GNU General Public License version 2 or later
 */

namespace Jyotish\Ganita;

use DateTime;
use Jyotish\Ganita\Math;
use Jyotish\Ganita\Time;

/**
 * Formulas for various astronomical calculations.
 *
 * @author Kunjara Lila das <vladya108@gmail.com>
 */
class Astro {
    /**
     * Approximate duration of precession in years.
     */
    const DURATION_PRECESSION = 25880;
    
    /**
     * Duration of the year in Gregorian calendar in days. 
     */
    const DURATION_YEAR_GREGORIAN = 365.2425;
    /**
     * Duration of the year in Julian calendar in days. 
     */
    const DURATION_YEAR_JULIAN = 365.25;
    /**
     * Duration of sidereal year in days.
     */
    const DURATION_YEAR_SIDEREAL = 365.2564;
    
    /**
     * Duration of the sidereal month in days.
     */
    const DURATION_MONTH_SIDEREAL = 27.3216610;
    /**
     * Duration of the synodic month in days.
     */
    const DURATION_MONTH_SYNODIC = 29.5305882;
    
    /**
     * Get sunrise time.
     * 
     * @param array $userData
     * @param array $sunData
     * @return type
     */
    static public function getSunRise($userData, $sunData)
    {
        $hourAngle = acos((cos(Math::dmsToDecimal(array('d' => 90, 'm' => 51))) - sin($userData['latitude']) * sin($sunData['declination'])) / cos($userData['latitude']) * cos($sunData['declination']));
        $eot = self::getEot($userData['date']);

        $time = 12 - $hourAngle + $eot;

        return $time;
    }
    
    /**
     * Get equation of time.
     * 
     * @param string $date A date/time string
     * @return float Number of minutes
     * @link https://en.wikipedia.org/wiki/Equation_of_time Equation of time
     */
    static public function getEot($date)
    {
        $dateObject = new DateTime($date);
        $day = $dateObject->format('z') + 1;

        $B = 2 * M_PI * ($day - 81) / 365;
        $E = 7.53 * cos($B) + 1.5 * sin($B) - 9.87 * sin(2 * $B);

        return $E;
    }
    
    /**
     * Get tiithi using the Harvey formula.
     * 
     * @param ind $day
     * @param int $month
     * @param int $year
     * @return int
     */
    static public function getTithiByHarvey($day, $month, $year)
    {
        if ($month <= 2) {
            $monthH	= $month + 12;
            $yearH = $year - 1;
        } else {
            $monthH = $month;
            $yearH = $year;
        }

        $eq  = floor($yearH/100);
        $eq1 = floor($eq/3) + floor($eq/4) + 6 - $eq;
        $eq2 = (round(($yearH/$eq - floor($yearH/$eq)) * 209) + $monthH + $eq1 + $day)/30;

        $tithi = round(($eq2 - floor($eq2))*30 + 1);

        return $tithi;
    }
    
    /**
     * Get zodiac sign in Western astrology.
     * 
     * @param int $day
     * @param int $month
     * @return int
     */
    static public function getSign($day, $month)
    {
        $signs = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        
        $signStart = [
            1 => 21, 2 => 20, 3 => 21, 
            4 => 21, 5 => 22, 6 => 22, 
            7 => 23, 8 => 22, 9 => 24, 
            10 => 24, 11 => 23, 12 => 23
        ];
        
        return $day < $signStart[$month] ? $signs[$month-1] : $signs[$month];
    }
    
    /**
     * Get angular speed of earth precession.
     * 
     * @param int $duration Approximate duration of precession in years
     * @return float
     */
    static public function getPrecessionSpeed($duration = self::DURATION_PRECESSION)
    {
        $arcsec = 360 / $duration * 3600;
        
        return $arcsec;
    }
    
    /**
     * Get Local Sidereal Time.
     * 
     * @param null|DateTime $Date Date (optional)
     * @param float $longitude Longitude of place (optional)
     * @return float In hours
     */
    static public function getLST(DateTime $Date = null, $longitude = 0)
    {
        $hour = $Date->format('G');
        $minute = $Date->format('i');
        $second = $Date->format('s');
        
        $JD = Time::getJD($Date);
        $T = ($JD - 2451545) / 36525;
        $GST = 24110.54841 + 8640184.812866 * $T + 0.093104 * $T * $T - 0.0000062 * $T * $T * $T;
        
        $units = Math::partsToUnits($GST, 86400);
        
        $hourS0     = $units['parts'] / 3600;
        $hourLng    = $longitude / 15;
        $hourOffset = $Date->getOffset() / 3600;
        $hourUT     = $hour + $minute / 60 + $second / 3600 - $hourOffset;
        
        $LST = $hourS0 + $hourLng + $hourUT * 1.002737909350795;
        
        $result = $LST >= 24 ? $LST -= 24 : $LST;
        
        return $result;
    }
    
    /**
     * Get Right Ascension of the Midheaven.
     * 
     * @param null|DateTime $Date Date (optional)
     * @param float $longitude Longitude of place (optional)
     * @return float In degree
     */
    static public function getRAMC(DateTime $Date = null, $longitude = 0)
    {
        $LST = self::getLST($Date, $longitude);
        $RAMC = $LST * 15;
        
        return $RAMC;
    }
}
