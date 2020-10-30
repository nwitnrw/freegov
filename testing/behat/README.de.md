Behat Testing
-------------

### Setup

Folgende Binärdateien werden benötigt:
* Chromedriver für das jeweilige Betriebssystem [herunterladen](https://sites.google.com/a/chromium.org/chromedriver/downloads).
* Behat: https://github.com/Behat/Behat (wird per Composer geladen)

Die *composer.json* Datei anpassen, sodass die *composer.json* Datei mit den Behat Abhängigkeiten geladen wird.

#### Auf Host Maschine Bash Alias einrichten
```
alias chromedriver-start='~/Dev/chromedriver --url-base=wd/hub --port=4444 --whitelisted-ips=""'
```

#### Composer Abhängigkeiten und Autoloading anpassen
In `composer.json` Datei des Projektes die `composer.json` Datei für die Abhängigkeiten der Behat Tests inkludieren. 
Nur die eine Zeile
innerhalb des `include` Blocks eintragen.

```
    "merge-plugin": {
        "merge-extra": true,
        "merge-extra-deep": true,
        "include": [
            
            ...
            
            "testing/behat/composer.json"
        ]
    }
```

In der `composer.json` Datei das Autoloading der `Context` Klassen einstellen:
```
  "autoload-dev": {
    "psr-4": {
      "Drupal\\Tests\\Behat\\Context\\": "testing/behat/context"
    }
  }
```
### Netzwerk IP der Host Maschine in behat.yml Datei eintragen
```
  ifconfig
```
Zeigt die IPs an. Es ist für gewöhnlich die erste IP in der Aufzählung.

### Ausführen der Tests
Tests direkt in Gast-System über Behat Kommando ausführen (aus dem *project* Verzeichnis):
```
bin/behat
```

_Tipp:_ Mit dem `--strict` Parameter werden sämtliche Errors ausgegeben. Optional kann der Pfad
zu einer Config Datei übergeben werden.
```
./behat -vvv --strict --config /var/www/project/docroot/behat.yml
```

## Smoke Tests

Grundsätzlich: Smoke Testing ist auch bekannt als "Build Verification Testing". Das Smoke Testing wird in deGov mit 
Behat Tests durchgeführt. Damit kann nach einem Build-Vorgang des Projektes (wie z.B. nach Software-Updates) schnell
überprüft werden, ob die wichtigsten Funktionen der deGov Instanz funktionieren. Da keine Inhalte angelegt oder
verändert werden, können sogar Live- oder Stage-Instanzen getestet werden.

Beispielkommando zum Ausführen der Smoke Tests:
```
./behat -vvv --strict --config behat-smoke-tests.yml
```

In der o.g. Behat Konfigurationsdatei kann mit dem `base_url` Attributwert die URL der Website eingestellt werden.
Auch werden in jener Konfigurationsdatei die Admin-Zugangsdaten der deGov Instanz hinterlegt. Sodass sich Behat auf der
(Live-)Website anmelden und das Backend überprüfen kann.

Zum Ausführen der Behat Smoke Tests, müssen die Tests aus einer deGov Instanz gestartet werden, deren `docroot`
Verzeichnis in der Behat-Konfigurationsdatei mittels dem Schlüssel `files_path` verwiesen wird. Denn es wird ein Drupal
Bootstrap durchgeführt, um die Testumgebung zu laden.

## Troubleshooting

### Chrome DevTools während der Testausführung benutzen
Dazu muss man in der Host-Maschine während des Tests folgendes Kommando ausführen:
```
killall php
```
Damit stoppt der Test und man sieht den aktuellen Stand im Browser und kann die Chrome DevTools benutzen. Ansonsten
stürzt der Browser ab.

### Chrome Browser startet nicht
* Kontrollieren ob die IP des Host-Systems in der `behat.yml` Datei stimmt (Schlüssel wd_host`). Wenn man in ein anderes Netzwerk wechselt (z.B. im Homeoffice),
  dann ändert sich die IP der Host-Maschine und muss entsprechend angepasst werden.

### Fehlermeldung
```
  [Behat\Behat\Context\Exception\ContextNotFoundException]       
  `FeatureContext` context class not found and can not be used.  
                                                                
```
Prüfen ob die Kontext Klassen in der `behat.yml` Datei referenziert sind.

### Quellen für Hintergrundinfos
* Drupal Extension für Behat und Mink: http://behat-drupal-extension.readthedocs.io/en/3.1/index.html
* Behat Projekt: http://behat.org/en/latest/
* Chromedriver: https://sites.google.com/a/chromium.org/chromedriver/downloads
* Smoke Testing: http://softwaretestingfundamentals.com/smoke-testing/
* Bitbucket Pipelines Dokumentation: https://confluence.atlassian.com/bitbucket/build-test-and-deploy-with-pipelines-792496469.html