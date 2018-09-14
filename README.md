# i-net HelpDesk HTML Formular

Dieses Projekt enthält den Beispielcode für die Implementierun eines HTML-Formulars um Daten die von einem Endkunden abgefragte werden via PHP-Server-Script an eine Email Adresse zu verschicken.

Eine i-net HelpDesk Instanz kann diese Email anschließend einlesen und via JavaScript-Trigger in ein aufbereitetes Ticket (Auftrag) umwandeln.

Dies ermöglich das Vorbelegen von Auftrags-Feldern entsprechend den Anforderungen der Support leistenden Gegenstelle.

## WARNUNG

Die hier skizzierten Programmteile sollten nicht so wie sie sind in einer Live-Umgebung eingesetzt werden. Sie dienen lediglich dem Umreißen der Funktion des Versendens einer vorkonfigurierten Email an den i-net HelpDesk.

## Voraussetzungen

Das Script-Ensemble benötigt einen Webserver, der mit PHP läuft. Der Vorgang beinhaltet das Versenden einer Email, welches funktionieren muss.

## Erklärung des Formulars

Das Formular wird in unserem Beispiel anhand einer Objekt-Struktur erzeugt. Diese sieht wie folgt aus (siehe [script.js](https://github.com/i-net-software/i-net-helpdesk-formular-demo/blob/master/script.js)):

```javascript
// Liste der einzelnen Formular-Zeilen
var lines = [ <FORM LINE>, <FORM LINE>, ... ]

// Format einer Formular-Zeile
{
    name:     "<NAME>",
    type:     "<TYPE>",
    label:    "<LABEL>",
    value:    "<VALUE>",		
    multiple: "multiple",
    choices:  [ <CHOICE>, <CHOICE>, ... ]
}

// Format von <CHOICE>
{
    label:    "<LABEL>",
    subnodes: [ <FORM LINE>, <FORM LINE>, ... ]
}
```

| Formular Element | Bedingung | Beschreibung |
|------------------|-----------|--------------|
| ```name```       | zwingend  | Name des Formular Feldes |
| ```type```       | zwingend  | Typ des Feldes: ```text```, ```submit```, ```file```, ```textarea```, ```choice``` |
| ```label```      | optional  | Ein Text-Label vor dem Formularfeld; beim Typ ```choice``` ein Eintrag in der Auswahlliste |
| ```value```      | optional  | Vorbelegter Wert eines Feldes. Wirkt nur bei den Typen ```text```, ```submit``` |
| ```multiple```   | optional  | Wirkt nur beim Typ ```file```. Erlaubt das Auswählen mehrerer Dateien |
| ```description```   | optional  | Beschreibung die nur bei Fehlern angezeigt wird |
| ```choices```   | zwingend für Typ ```choice``` | Liste der Auswahlmöglichkeiten |
| ```subnodes ```   | optional | Liste weiterer Formular-Elemente und -Zeilen. Diese wird eingeblendet, wenn der entsrpechende Eintrag ausgewählt wurde. Dies kann beliebig verschachtelt werden. |
