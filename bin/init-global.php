<?php

// Globale Skripte einbinden
require_once(dirname(__FILE__).'/../dist/include/autoloader.php');
require_once(DIR_INCLUDE.'cli.php');

if ( checkCorrectFileRights() ) {

  $hSettings = askGlobalQuestions();
  if ( $hSettings ) {
    updateGlobalSettings($hSettings);
  }

  $hSettings = askAdditionalQuestions();
  if ( $hSettings ) {
    updateGlobalSettings($hSettings);
  }

}


function askGlobalQuestions() {
  return array(
    'hauptfarbe' => askColor(array(
      "Damit der Shop in Ihrer CI erstrahlt benoetigen wir Ihre Hauptfarbe. ",
      "Anhand Ihrer Hauptfarbe werden Links, Hintergruende und Bilder coloriert. ",
      "Bitte geben Sie Ihre Hauptfarbe als Hex-Farbcode an: (Beispiel: #027a8b) ",
    )),
    'akzentfarbe' => askColor(array(
      "Fuer Aktionslinks und wichtige Elemente Ihres Shops bieten wir den Einsatz ",
      "einer Akzentfarbe an. ",
      "Bitte geben Sie Ihre Akzentfarbe als Hex-Farbcode an: (Beispiel: #f59000) ",
    )),
    'farben-invertieren' => askBool(array(
      "Auf Wunsch koennen Sie die Gestaltung des Shops auch invertieren (heller ",
      "Text auf dunklem Hintergrund), Ihre Farben bleiben dabei erhalten. ",
      "Moechten Sie die Farben des Shops invertieren? (j/n): ",
    )),
    'auftrags-url-zum-rp-system' => askUrl(array(
      "----------",
      "",
      "Um die Produkte und Preise aus Ihrem RP-System ausgeben zu koennen, ",
      "benoetigen wir eine Anbindung via API zu Ihrem RP-System. Dazu muss Ihr ",
      "Webspace curl-Verbindungen zu Ihrem ResellerProfessional zulassen. ",
      "Bitte geben Sie die vollstaendige URL mit Ihrer Auftragsnummer an: ",
      "(Beispiel: https://123456.premium-admin.eu) ",
    )),
    'token-fuer-rp-verbindung' => ask(array(
      "Zum Schutz Ihrer Daten werden Aufrufe an die API Ihres RP-Systems nur als ",
      "authentifizierter Benutzer oder Gast mit Auth-Token erlaubt. ",
      "Der Shop verwendet zur Anmeldung den Gast-Benutzer mit dem Auth-Token, das ",
      "Sie in den RP-Systemeinstellungen unter System > API angeben koennen. ",
      "Bitte geben Sie Ihr Gast-Token fuer API-Aufrufe an: (Beispiel: abcdef1234) ",
    )),
  );
}

function askAdditionalQuestions() {
  $bShouldAskMoreQuestions = askBool(array(
    "",
    "Herzlichen Glueckwunsch, Sie haben alle benoetigten Daten angegeben, damit ",
    "Ihr Shop seinen Dienst leisten kann. ",
    "Um Ihren Shop noch mehr Ihre Handschrift zu verleihen, empfehlen wir Ihnen, ",
    "in der global.ini weitere Angaben zu Ihrem Unternehmen und Shop, einzutragen. ",
    "Diese Angaben betreffen u.a. Kontaktdaten, weitere URLs und Ihre Firma. ",
    "Moechten Sie diese Daten nun direkt im Installationsvorgang angeben? (j/n): ",
  ));

  if ( $bShouldAskMoreQuestions === 'false' ) {
    say(array(
      "Sie koennen jederzeit die dist/settings/global.ini oeffnen und saemtliche ",
      "Angaben anpassen. Sie erkennen einige auszufellende Werte z.B. an Stellen ",
      'wie "{{unternehemensname}}" in Ihrem Shop. '
    ));
    return false;
  }

  return array(
    'vollstaendiger-unternehmensname' => ask(array(
      "Bitte geben Sie fuer Seiten mit Gesetzestexten (wie z.B. AGB, Impressum) ",
      'einen vollstaendigen Unternehmensnamen mit Rechtsform an ',
      '(Beispiel: ResellerShop GmbH & Co. KG): ',
    )),
    'unternehmensname' => ask(array(
      "Bitte geben Sie den Unternehmensnamen an, der in Fliesstexten verwendet ",
      "werden soll (Beispiel: ResellerShop): ",
    )),
    'kurzer-unternehmensname' => ask(array(
      "Bitte geben Sie einen abgekuerzten Unternehmensnamen an, der zur Variation ",
      "im Fliesstext verwendet wird (Beispiel: RP-Shop): ",
    )),
    'empfaenger-von-kontaktanfragen' => ask(array(
      "",
      "Auf dem Shop befinden sich Kontaktformulare, mit denen Ihre Kunden und ",
      "Interessenten direkt mit Ihnen kommunizieren koennen. Sendet ein Benutzer ",
      "das Formular ab, so wird an eine hinterlegte E-Mail-Adresse eine ",
      "Nachricht gesendet. ",
      "Bitte geben Sie die E-Mail-Adresse des Empfaengers von ",
      "Kontaktnachrichten an: (Beispiel: reseller@shop.eu) ",
    )),
    'url-zu-ihrer-website' => askUrl(array(
      "",
      "Fuer Verlinkungen auf Ihre regulaere Internetpraesenz (z.B. bei kritischen ",
      "Fehlern) koennen Sie die vollstaendige URL zu Ihrer Website angeben: ",
      "(Beispiel: https://www.reseller-shop.eu) ",
    )),
    'url-zu-ihrem-shop' => askUrl(array(
      "Bitte geben Sie die URL an, unter welcher der Shop oeffentlich erreichbar ",
      "sein wird: (Beispiel: https://demo.reseller-shop.eu/shop/) ",
    )),
    'offizielle-url-zum-rp-system' => askUrl(array(
      "Nutzen Sie eine eigene Subdomain fuer Ihr RP-System, so wird diese fuer ",
      "die Weiterleitung zu Ihrem RP beim Login verwendet. Wenn Sie nur die ",
      "URL mit Auftragsnummer verwenden, so verwenden Sie bitte diese hier. ",
      "Bitte geben Sie die offizielle URL an, unter der Ihr ResellerProfessional-",
      "System erreichbar ist: (Beispiel: https://demoshop.premium-admin.eu) ",
    )),
  );
}



function checkCorrectFileRights() {
  say("Pruefe, ob die wichtigsten Schreibrechte gesetzt sind...");

  // Verzeichnisse, die geprueft werden sollen,
  // MUESSEN mit einem Slash enden!
  $asWritableFiles = array(
    // Cookie für RPC-Verbindungen
    // benötigt von dist/include/bb.rpc.class.php
    'dist/bb.rpc.cookie',
    // Verzeichnis kompilierter Templates
    // benötigt von Renderer
    'dist/compiled/',
    // Colorierte Bilder
    // benötigt für bin/color-update.sh
    'dist/images/',
    // Globale Einstellungen
    // benötigt für bin/init-global.php
    'dist/settings/global.ini',
    // Farbeinstellungen
    // benötigt von bin/color-update.sh
    'src/scss/_ci.scss',
  );

  $asUnwritableFiles = array();

  foreach ( $asWritableFiles as $sWritableFile ) {
    $sPath = dirname(__FILE__).'/../'.$sWritableFile;
    // Dürfen bestehende Dateien überschrieben werden?
    if ( file_exists($sPath) ) {
      if ( !is_writable($sPath) ) {
        $asUnwritableFiles[] = $sPath;
      }
    } else {
      // Darf Verzeichnis erstellt werden?
      if ( substr($sPath, -1) === '/' ) {
        if ( !mkdir($sPath, 740) ) {
          $asUnwritableFiles[] = $sPath;
        }
      // Darf Datei erstellt werden?
      } else {
        if ( !touch($sPath) ) {
          $asUnwritableFiles[] = $sPath;
        }
      }
    }
  }

  if ( count($asUnwritableFiles) ) {
    say('Bitte korrigieren Sie die Schreibrechte fuer folgende Dateien und Ordner: ');
    say($asUnwritableFiles);

    // Ist die global.ini nicht beschreibbar, so koennen wir gleich aufhören.
    if ( in_array('dist/settings/global.ini', $asUnwritableFiles) ) {
      say(array(
        '',
        'Damit der Konfigurationsassistent kann nicht fortgefuehrt werden, ',
        'da die global.ini nicht vom aktuellen PHP-Prozess beschrieben werden darf. ',
        '',
        'Bitte setzen Sie die korrekten Schreibrechte fuer die dist/settings/global.ini. ',
        'Informationen hierzu erhalten Sie unter ',
        'https://doku.premium-admin.eu/doku.php/handbuch/shop_2.0/installation/start#schreibrechte_setzen_optional',
      ));
      exit(1);
    }

    return false;
  }

  return true;
}
