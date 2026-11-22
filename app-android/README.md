# Pannello Katy — app Android

`PannelloKaty.apk` è l'app del pannello di gestione: una Trusted Web Activity che apre
`https://katysnails.it/admin/` a tutto schermo con il motore di Chrome (fotocamera per i QR dei buoni,
caricamento foto, sessione: tutto come nel browser, senza barra dell'indirizzo).

- Installazione: aprire l'APK sul telefono, consentire l'installazione da quell'origine, installare.
- Tenendo premuta l'icona: "Leggi un buono", "Buoni regalo", "Aggiungi foto".
- Sul sito deve esserci `/.well-known/assetlinks.json` (sta in `da-caricare/`) con l'impronta della chiave di firma.
- Pacchetto `it.katysnails.pannello`, versione 1.0 (versionCode 1), Android 7 o più recente.

Per ricostruirla: Java 17/21, Gradle 8.9+, Android SDK (piattaforma 35, build-tools 35), poi

```bash
ANDROID_HOME=/percorso/sdk KN_KEYSTORE=/percorso/katysnails-pannello.keystore KN_KEYSTORE_PASSWORD=... gradle assembleRelease
```

Il file `.keystore` e la password non sono nel repository: vanno conservati a parte. Una nuova versione si
installa sopra la precedente solo se firmata con la stessa chiave; aumentare `versionCode` in `app/build.gradle`.
