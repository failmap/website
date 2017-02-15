<?php
require_once("../vendor/autoload.php");
require_once('../configuration.php');
require_once('../configuration.php');
include_once('../code/statistics.php');
include_once('../code/functions.php'); // not the best pattern, got 2 php :)
set_error_handler('myErrorHandler');
?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>Faalkaart, geeft inzicht in beveiligde verbindingen van gemeentes.</title>

        <link rel="stylesheet" type="text/css" href="css/tooltipster.css" />
        <link rel="stylesheet" type="text/css" href="css/themes/tooltipster-light.css" />
        <link rel="stylesheet" type="text/css" href="css/leaflet.css" />
        <link rel="stylesheet" type="text/css" href="css/bootstrap_v3.3.6.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7">
        <link rel="stylesheet" type="text/css" href="css/bootstrap-theme_v3.3.6.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r">

        <script type="text/javascript" src="scripts/jquery_v2.2.0.min.js"></script>
        <script type="text/javascript" src="scripts/bootstrap_v3.3.6.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"></script>
        <script type="text/javascript" src="scripts/jquery.tooltipster.min.js"></script>
        <script type="text/javascript" src="scripts/leaflet.js"></script>

        <!-- custom libraries / files -->
        <script type="text/javascript" src="scripts/mapdata.php?t=<?php echo date("Ymdh"); ?>"></script> <!-- You can rip this, but also check our github repo :) -->
        <script type="text/javascript" src="scripts/tooltips.php?t=<?php echo date("Ymdh"); ?>"></script>

        <!-- leaflet user styling -->
        <style>
            #map { width: 1100px; height: 900px; }
            .info {
                min-width: 280px;
                padding: 6px 8px; font: 14px/16px Arial, Helvetica, sans-serif;
                background: white; background: rgba(255,255,255,0.8);
                box-shadow: 0 0 15px rgba(0,0,0,0.2);
                border-radius: 1px; }
            .info h4 { margin: 0 0 5px; color: #777; }
            .legend { text-align: left; line-height: 18px; color: #555; }
            .legend i { width: 18px; height: 18px; float: left; margin-right: 8px; opacity: 0.7; }
        </style>
        <!-- /leaflet user styling -->

        <?php
            if (isset($refreshTimer) and ($refreshTimer > 0)){
                print "<meta http-equiv=\"refresh\" content=\"".$refreshTimer."\">";
            }
        ?>
    </head>

    <body role="document">

        <nav class="navbar navbar-default">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">Faalkaart</a>
                </div>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="#">Home</a></li>
                        <li><a href="#kaart">Kaart</a></li>
                        <li><a href="#balk">Balk</a></li>
                        <li><a href="#cijfers">Cijfers</a></li>
                        <li><a href="#domeinen">Domeinen</a></li>
                        <li><a href="#uitleg">Uitleg</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </nav>

    <div class="container theme-showcase" role="main">

        <div class="jumbotron">
            <h1>Faalkaart</h1>
         <p>Faalkaart geeft inzicht in hoe veilig uw gemeente is richting het internet. Er wordt gekeken hoe veilig de gemeente haar verbindingen heeft ingericht. Het is belangrijk dat dit goed
gebeurt omdat hierover ook uw gegevens worden verstuurd.</p>
            <p>Stuur nieuwe subdomeinen in via twitter: <a href="https://twitter.com/faalkaart">@faalkaart</a> of mail <a href="mailto:info@faalkaart.nl?subject=subdomeinen">info@faalkaart.nl</a></p>

            <p><small>Update 15 februari 2017: Inmiddels wordt er weer <a href="https://github.com/failmap" target="_blank">volop gewerkt</a> aan faalkaart. De kaart is bijgewerkt naar nieuwe, goed onderhoudbare, technieken. Inmiddels is er een <a href="https://internetcleanup.foundation" target="_blank">stichting opgericht</a> om de ontwikkeling van de kaart te stimuleren. Binnenkort wordt er gewerkt aan het beter scannen van e.e.a: er gaat meer en sneller gescand worden.</small></p>
        </div>

        <div class="page-header">
            <a name="kaart"></a>
            <h1>De Kaart</h1>
            <p>Deze kaart is te lezen als een stoplicht. Iedere gemeente is weergegeven als een kleur. Rood betekent onvoldoende, groen betekent voldoende. Nuances daargelaten, zie onderaan.</p>
        </div>

        <div id='map'></div>
        <script type="text/javascript" src="scripts/failmapstyling.js?t=<?php echo date("Ymdh"); ?>"></script>

        <div class="page-header">
            <a name="balk"></a>
            <h1>De Balk</h1>
            <p>Deze balk geeft in percentages aan hoe het er voor staat. Het kan zijn dat en gemeente 1 oranje en 20 groene verbindingen heeft. Dat is beter zichtbaar in deze balk.</p>
        </div>

        <?php
            $Stats = new Statistics();
            $results = $Stats->goBack(0,'municipality');

            $red = $results['red'];
            $orange = $results['orange'];
            $green = $results['green'];
            $total = $results['total'];

            $total = $red + $green + $orange;

            $progressRed = floor(($red/$total)*100);
            $progressOrange = floor(($orange/$total)*100);
            $progressGreen = floor(($green/$total)*100);

            // due to rounding we might miss a little... so fill it up on the positive side...
            if ($progressRed+$progressOrange+$progressGreen<100) $progressGreen += 100 - ($progressRed+$progressOrange+$progressGreen);
        ?>

        <div class="progress">
            <div class="progress-bar progress-bar-success" style="width: <?php print $progressGreen;?>%"><span class="sr-only">35% Complete (success)</span></div>
            <div class="progress-bar progress-bar-warning" style="width: <?php print $progressOrange;?>%"><span class="sr-only">20% Complete (warning)</span></div>
            <div class="progress-bar progress-bar-danger" style="width: <?php print $progressRed;?>%"><span class='sr-only'>10% Complete (danger)</span></div>
        </div>


        <div class="page-header">
            <a name="cijfers"></a>
            <h1>De Cijfers</h1>
            <p><i>Let op: verwijderde/opgeruimde subdomeinen en endpoints staan niet in deze statistieken. Dit zijn er ongeveer 10, wat toeneemt over tijd.</i></p>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Wanneer</th>
                        <th>Domeinen</th>
                        <th>Voldoende</th>
                        <th>Matig</th>
                        <th>Onvoldoende</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    function showPreviousNumbers($days, $label, $category) {
                        ob_start();
                        $Stats = new Statistics();
                        $previousDay = $Stats->goBack($days,$category);
                        ?>
                        <tr>
                            <td><?php print $label;?></td>
                            <td><?php print $previousDay['total'];?></td>
                            <td><?php print $previousDay['green'];?> <small>(<?php print percentage($previousDay['green'],$previousDay['total']);?>%)<small></td>
                            <td><?php print $previousDay['orange'];?> <small>(<?php print percentage($previousDay['orange'],$previousDay['total']);?>%)<small></td>
                            <td><?php print $previousDay['red'];?> <small>(<?php print percentage($previousDay['red'],$previousDay['total']);?>%)<small></td>
                        </tr>
                        <?php
                        return ob_get_clean();
                    }

                    print showPreviousNumbers(0,"Laatste Stand","municipality");
                    print showPreviousNumbers(1,"Gisteren","municipality");
                    print showPreviousNumbers(7,"Afgelopen week","municipality");
                    print showPreviousNumbers(31,"Afgelopen Maand","municipality");
                    print showPreviousNumbers(62,"Twee Maanden","municipality");
                    print showPreviousNumbers(93,"Drie Maanden","municipality");
                    print showPreviousNumbers(124,"Vier Maanden","municipality");
                    print showPreviousNumbers(155,"Vijf Maanden","municipality");
                    print showPreviousNumbers(186,"Half Jaar","municipality");

                    ?>
                    </tbody>
                </table>
            </div>
        </div>


        <div class="col-md-6">
            <div class="page-header">
                <a name="uitleg"></a>
                <h1>Over Faalkaart</h1>
            </div>

            <h2>Waarom Faalkaart?</h2>
            <p>Antwoord: Het is boeiend om te zien wat de status is van veiligheid van dataverbindingen van publieke diensten, daar wordt namelijk vaak gevoelige informatie verwerkt. De kaart heet Faalkaart omdat initieel werd aangenomen dat er nog veel te verbeteren viel en een tendentieuze naam eerder leidt tot actie dan een schattige naam. De naam bleek verkeerd gekozen.</p>

            <h2>Mijn gemeente is rood, wat nu?</h2>
            <p>Antwoord: Helaas zit er een gat in de muur: de gemeente zal actie moeten ondernemen om e.e.a. goed in te richten of een <a href="https://letsencrypt.org">nieuw certificaat te installeren</a>. Er wordt uitgegaan van de zwakste schakel: ergens een onvoldoende betekent een rode vlek op de kaart.</p>

            <h2>Mijn gemeente is groen, is alles goed?</h2>
            <p>Antwoord: De kans dat een subdomein mist, en juist daar een gat in de muur zit, is aanwezig. De vuistregel is dat meer subdomeinen meer zekerheid geven.</p>

            <h2>Hoe compleet is dit?</h2>
            <p>Antwoord: Er zijn meer dan 1800 domeinen getest van 350+ gemeenten. Er wordt getest op poort 443 (https). De geteste domeinen staan links op deze site vermeld. Er is alleen getest op *.gemeentenaam.tld, dus niet op doorverwijzingen. Sommige diensten zitten achter ip- en certificaatfiltering en zijn dus niet testbaar zonder de juiste voorwaarden. Sommige gemeenten accepteren DNS wildcards, hiervan zijn alleen www.~ en ~ getest: dit is dus incompleet en kan voor deze gemeenten de schijn wekken dat alles in orde is. De kaartdata en gemeente-websites komen uit 2014 en het is mogelijk dat er dus wat mist door o.a. het samengaan van gemeenten.</p>

            <h2>Hoe komt de score tot stand?</h2>
            <p>Antwoord: Er wordt verbinding gemaakt op poort 443. Als er een verbinding is, en er is een beveiligde verbinding bedoeld, dan wordt gecontroleerd hoe goed die verbinding is. Als er een beveiligde verbinding is, dan wordt deze geacht als nodig. Het is lastig om automatisch te bepalen of een domein wel of geen beveiligde verbinding vereist: publieke informatie moet ook bereikbaar zijn voor mensen die internetten via een aardappel. </p>

            <h2>Hoe moet SSL/TLS worden ingericht?</h2>
            <p>Antwoord: Er is een <a href="https://www.google.nl/search?q=secure+tls+configuration&oq=secure+tls+configuration&aqs=chrome..69i57.6276j0j1&sourceid=chrome&es_sm=93&ie=UTF-8">aantal goede handleidingen te vinden</a>. Toonaangevend advies komt van het <a href="https://www.ncsc.nl/actueel/whitepapers/ict-beveiligingsrichtlijnen-voor-transport-layer-security-tls.html">Nationaal Cyber Security Centrum</a>. Een variant toegespitst op gemeenten, die ook ingaat op DNSSEC, staat op de site van <a href="https://www.ibdgemeenten.nl/3619-2/" target="_blank">IBD Gemeenten</a>. Lijsten met goede instellingen zijn te vinden op <a href="https://cipherli.st">Cipher List</a>.</p>

            <h2>Sinds wanneer bestaat deze kaart?</h2>
            <p>Antwoord: De kaart is gepresenteerd door sprekers op de "<a href="http://inhethoofdvandehacker.nl/" target="_blank">in het hoofd van de hacker</a>" conferentie van 16 maart 2016. Een volledig programma van de conferentie is terug te zien op de site van de conferentie.</p>

            <h2>Hoe is dit tot stand gekomen?</h2>
            <p>Antwoord: Dit project is tot stand gekomen door:</p>
            <ul>
                <li>Programmeer, hak en breekwerk door Elger Jonker</li>
                <li>DNSSEC en nuttig ongevraagd advies: Eelko Neven</li>
                <li>Actuele lijst gemeenten: 200ok.nl</li>
                <li>Beoordeling op veiligheid door Qualys SSL labs</li>
                <li>URLs en polygonen van de klikbare gemeentekaart van Imergis. Kaartdata 2014.</li>
                <li>Simpele DNS verkenning met DNS Recon</li>
                <li>Styling: Twitter Bootstrap</li>
                <li>Talen: Python, PHP op MariaDB.</li>
                <li>JQuery MapHighlight door David Lynch</li>
            </ul>

            <h2>Wat is de historie van Faalkaart?</h2>
            <p>
            <p><small><b>7 augustus 2016</b>: Faalkaart heeft de steun gekregen van het SIDN fonds, we zullen het komende jaar de kaart uitbreiden en op veel meer controleren. We gaan de kaartrot oplossen en zorgen dat het makkelijk wordt om zelf de kaart te kunnen draaien (onafhankelijk). Ook is de chaching van de site ingevoerd, dus het voelt weer snel(ler) aan.</small></p>
            <p><small><b>9 juni 2016</b>: Door een nieuwe kwetsbaarheid zijn er 100+ domeinen in het rood beland, van 2% naar 9% kwetsbaar dus. Het aantal matige domeinen blijft gelukkig afnemen. Hoe lang zal het duren tot alles gepatched is? Wie patcht het laatst?</small></p>
            <p><small><b>Extra update</b>: Faalkaart heeft een projectbijdrage gevraagd aan het SIDN fonds om er voor te zorgen dat dit middel breder en makkelijker kan worden ingezet. We gaan hierdoor vele honderdduizenden kwetsbaarheden aan de kaak te stellen en blijven motiveren om ze te verhelpen. De techneuten, hackers en nerds achter faalkaart staan te trappelen om het internet robuuster te maken. Half Juni weten we meer. Spannend!</small></p>
            <p><small><b>Extra update 2</b>: We zien dat door de grote hoeveelheid data we caching moeten gaan toepassen en verder moeten optimaliseren. De bedoeling is om de kaart zo actueel mogelijk weer te geven. Tot dit opgelost is zal het iets langer duren voordat de kaart geladen is.</small></p>
            <p><small><b>8 april 2016</b>: Het aantal domeinen met een onvoldoende is gezakt naar 2%, was ooit 8%. Er zijn zojuist 1200 domeinen toegevoegd. Er is een team aan het ontstaan dat de faalkaart verder gaat uitbreiden en onderhouden. Vele handen maken licht werk. Dank aan gemeenten voor het insturen van subdomeinen. Dit is altijd welkom!</small></p>
            <p><small><b>25 maart 2016</b>: De kaart wordt automatisch ververst. Onder de uitleg staat een overzicht met domeinen die onvoldoende scoren.</small></p>
            <p><small><b>18 maart 2016</b>: De kaart wordt zeer binnenkort automatisch bijgewerkt. Nieuw zijn statistieken met historie. De domeinenlijst is verbeterd en er is tekst toegevoegd over de totstandkoming van het cijfer. Binnenkort ook open source.</small>
            <p><small><b>16 maart 2016</b>: De eerste serie van 1800 domeinen is geladen, dit wordt nog aangevuld en zal binnenkort opnieuw worden gecontroleerd. De testdatum is nu zichtbaar. De eerste verbeteringen schijnen een half uur na presentatie al te zijn doorgevoerd. Dat is stoer!</small></p>
            </p>

            <div class="page-header">
                <a name="takenlijst"></a>
                <h2>Wat scoort onvoldoende?</h2>
            </div>
            <table class="table table-striped">
                <thead>
                <tr><th>Gemeente</th><th>Domeinen / oordeel</th></tr>
                </thead><tbody>
                <?php

                $gradeColors = array("0" => "000000", "F" => "ff0000", "T" => "ff0000", "D" => "ff0000",  "C" => "FFA500",  "B" => "FFA500", "A-" => "00ff00", "A" => "00ff00","A+" => "00ff00","A++" => "00ff00");

                $previousGemeente = ""; $previousUrl = "";
                $i=0; // should use a template engine :)

                /**

                We want a query that:
                - Gives results to a certain date.
                - Gives only the latest result from the entire set.

                Vendor neutral and fast solution:
                http://stackoverflow.com/questions/121387/fetch-the-row-which-has-the-max-value-for-a-column

                SELECT organization, url.url as theurl, scans_ssllabs.ipadres, scans_ssllabs.servernaam, scans_ssllabs.scandate, scans_ssllabs.scantime, scans_ssllabs.rating as oordeel FROM `url` left outer join scans_ssllabs ON url.url = scans_ssllabs.url LEFT OUTER JOIN scans_ssllabs as t2 ON (scans_ssllabs.url = t2.url AND t2.scandate > scans_ssllabs.scandate  AND t2.scandate < '2016-03-19') WHERE t2.url IS NULL AND organization <> '' AND scans_ssllabs.scandate < '2016-03-19' order by organization ASC

                // with this one we browse through time and get only one result per url :) Time is by default NOW()

                 */

                // This selects the LAST SCANNED domain, but these can be multiple since a domain can have N endpoints.
                // this means that coloring is off, since the N-1 endpoint might have F and endpoint N may have A.
                // that means the domain will be falsely colored as A.

                // the solution was to add uniciteit (uniqueness) to the set of url, ip and port, and group-concating or maxing those
                // for the worst color the domain has.

                $sql = "SELECT
                          organization,
                          url.url as theurl,
                          scans_ssllabs.ipadres,
                          scans_ssllabs.servernaam,
                          scans_ssllabs.scandate,
                          scans_ssllabs.scantime,
                          min(scans_ssllabs.scanmoment) as scanmoment,
                          count(scans_ssllabs.rating) as endpointsfound,
                          max(scans_ssllabs.rating) as rating
                        FROM `url` left outer join scans_ssllabs ON url.url = scans_ssllabs.url
                        LEFT OUTER JOIN scans_ssllabs as t2 ON (
                          scans_ssllabs.url = t2.url
                          AND scans_ssllabs.ipadres = t2.ipadres
                          AND scans_ssllabs.poort = t2.poort
                          AND t2.scanmoment > scans_ssllabs.scanmoment
                          AND t2.scanmoment <= NOW())
                        WHERE t2.url IS NULL
                          AND organization <> ''
                          AND scans_ssllabs.scanmoment <= now()
                          AND url.isDead = 0
                          AND scans_ssllabs.isDead = 0
                          AND scans_ssllabs.rating IN ('T','F')
                        group by (scans_ssllabs.url)
                        order by organization ASC, rating DESC";
                $results = DB::query($sql);
                foreach ($results as $row) {

                    if ($previousGemeente != $row['organization']){
                        // vorige afsluiten
                        if ($i!= 0) {
                            print "</td></tr>";
                        }

                        print "<tr><td><a name=\"".$row['organization']."\"></a>".$row['organization']."</td><td>";
                    }

                    // todo: count number of urls and endpoints.
                    if ($previousUrl != $row['theurl']){
                        if (isset($gradeColors[$row['rating']])){
                            $colorOordeel = $gradeColors[$row['rating']];
                        } else {
                            $colorOordeel = "AAAAAA";
                        }

                        // show a nice (2x) if there are multiple endpoints. Amsterdam has redundant endpoints on both ipv4 and ipv6
                        if ($row['endpointsfound'] > 1) {
                            print "<div style='color: #" . $colorOordeel . "' id='" . makeHTMLId($row['theurl']) . "'>" . $row['theurl'] . " (" . $row['endpointsfound'] . "x)</div><small>".$row['scanmoment']."</small>";
                        } else {
                            print "<div style='color: #" . $colorOordeel . "' id='" . makeHTMLId($row['theurl']) . "'>" . $row['theurl'] . "</div><small> ".$row['scanmoment']."</small>";
                        }
                    }
                    $previousGemeente = $row['organization'];
                    $previousUrl = $row['theurl'];
                }

                print "</td></tr>";
                ?>
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <div class="page-header">
                <a name="domeinen"></a>
                <h1>Domeinen</h1>
            </div>
            <table class="table table-striped">
                <thead>
                <tr><th>Gemeente</th><th>Domeinen / oordeel</th></tr>
                </thead><tbody>
            <?php

            $gradeColors = array("0" => "000000", "F" => "ff0000", "T" => "ff0000", "D" => "ff0000",  "C" => "FFA500",  "B" => "FFA500", "A-" => "00ff00", "A" => "00ff00","A+" => "00ff00","A++" => "00ff00");

                $previousGemeente = ""; $previousUrl = "";
                $i=0; // should use a template engine :)

                /**

                 We want a query that:
                 - Gives results to a certain date.
                 - Gives only the latest result from the entire set.

                 Vendor neutral and fast solution:
                 http://stackoverflow.com/questions/121387/fetch-the-row-which-has-the-max-value-for-a-column

                 // with this one we browse through time and get only one result per url :) Time is by default NOW()

                 */

                // This selects the LAST SCANNED domain, but these can be multiple since a domain can have N endpoints.
                // this means that coloring is off, since the N-1 endpoint might have F and endpoint N may have A.
                // that means the domain will be falsely colored as A.

                // the solution was to add uniciteit (uniqueness) to the set of url, ip and port, and group-concating or maxing those
                // for the worst color the domain has.

                $sql = "SELECT
                          organization,
                          url.url as theurl,
                          scans_ssllabs.ipadres,
                          scans_ssllabs.servernaam,
                          scans_ssllabs.scandate,
                          scans_ssllabs.scantime,
                          count(scans_ssllabs.rating) as endpointsfound,
                          max(scans_ssllabs.rating) as rating
                        FROM `url` left outer join scans_ssllabs ON url.url = scans_ssllabs.url
                        LEFT OUTER JOIN scans_ssllabs as t2 ON (
                          scans_ssllabs.url = t2.url
                          AND scans_ssllabs.ipadres = t2.ipadres
                          AND scans_ssllabs.poort = t2.poort
                          AND t2.scanmoment > scans_ssllabs.scanmoment
                          AND t2.scanmoment <= now())
                        WHERE t2.url IS NULL
                          AND organization <> ''
                          AND scans_ssllabs.scanmoment <= now()
                          AND url.isDead = 0
                          AND scans_ssllabs.isDead = 0
                        group by organization, scans_ssllabs.url 
                        order by organization ASC, rating DESC";
                $results = DB::query($sql);
                    foreach ($results as $row) {

                        if ($previousGemeente != $row['organization']){
                            // vorige afsluiten
                            if ($i!= 0) {
                                print "<small><a href='#kaart'>terug naar de kaart...</a></small></td></tr>";
                                $i=0;
                            }

                            print "<tr><td><a name=\"".$row['organization']."\"></a>".$row['organization']."</td><td>";
                        }

                        // todo: count number of urls and endpoints.
                        if ($previousUrl != $row['theurl']){
                            if (isset($gradeColors[$row['rating']])){
                                $colorOordeel = $gradeColors[$row['rating']];
                            } else {
                                $colorOordeel = "AAAAAA";
                            }

                            // show a nice (2x) if there are multiple endpoints. Amsterdam has redundant endpoints on both ipv4 and ipv6
                            if ($row['endpointsfound'] > 1) {
                                print "<div style='color: #" . $colorOordeel . "' id='" . makeHTMLId($row['theurl']) . "'>" . $row['theurl'] . " (" . $row['endpointsfound'] . "x)</div> ";
                            } else {
                                print "<div style='color: #" . $colorOordeel . "' id='" . makeHTMLId($row['theurl']) . "'>" . $row['theurl'] . "</div> ";
                            }
                        }
                        $previousGemeente = $row['organization'];
                        $previousUrl = $row['theurl'];
                        $i++;
                    }

                print "<small><a href='#kaart'>terug naar de kaart...</a></small></td></tr>";
                ?>
                </tbody>
            </table>
        </div>


    </div>
</body>
</html>
<!--
This page was generated with MSPAINT.EXE on <?php echo date(DATE_RFC2822); ?> in <?php echo round((microtime(TRUE)-$_SERVER['REQUEST_TIME_FLOAT']), 4); ?>s
-->
