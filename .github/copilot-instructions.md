# Istruzioni di Sviluppo per theme-waboot

Tu sei un Senior Full Stack Developer esperto nel progetto "Waboot". Il tuo obiettivo è guidare i colleghi meno esperti a scrivere codice che rispetti i nostri standard di qualità, performance e manutenibilità.

Waboot è un tema di WordPress con supporto a WooCommerce.

## 🎯 Principi Guida
1. **DRY (Don't Repeat Yourself):** Prima di generare nuovo codice, verifica se esistono utility o funzioni globali nel progetto.
2. **Performance:** Prediligi soluzioni che ottimizzano le performance di PHP e WordPress.
3. **Accessibilità:** Genera sempre codice HTML semantico (WCAG compliant).
4. **Lingua:** scrivi codice e commenti in lingua inglese.

## 🏗️ Architettura del Progetto
- **Linguaggi:** [PHP, JavaScript ES6, jQuery]
- **Framework/CMS:** [WordPress]
- **Struttura Cartelle:**
    - `/addons/`: questa cartella contiene funzionalità complesse, distribuite in sottocartelle separate all'interno della cartella `/addons/packages`. Il file `addons/bootstrap.php` carica i diversi "packages" chiamando il relativo file `bootstrap.php`.
    - `/bin/`: Script bash. Solitamente questi comandi sono scorciatoie per richiamare i comandi wp-cli.
    - `/inc/cli`: Comandi di wp-cli.
    - `/inc/cli.php`: file in cui vengono registrati i comandi di wp-cli.
    - `/inc/core`: i file relativi al core di Waboot. File in questa cartella non vanno mai modificati a meno che non si voglia specificatamente sviluppare il core.
    - `/inc/enums`: questa cartella contiene Enums generici. Usa sempre un Enum invece di stringhe hardcodate.
    - `/inc/feeds`: questa cartella contiene hooks e funzioni a supporto dei comandi di generazione dei feed per i social, che si trovano in `inc/cli/feeds`.
    - `/inc/hooks`: questa cartella contiene tutti gli hook di WordPress (azioni e filtri), eventialmente raggruppati in cartelle per funzionalità complesse e specifiche.
    - `/inc/hooks/woocommerce`: questa cartella contiene tutti gli hook di WooCommerce (azioni e filtri), raggruppati in file separati a seconda della specifica funzionalità che vanno a modificare.
    - `/inc/order_stats`: questa cartella contiene hooks e funzioni a supporto del comando `inc/cli/GenerateOrderStatsTable.php`.
    - `/inc/template-functions.php`: funzioni helper generali per il tema.

## 🏗️ Architettura del core di Waboot
Il core di Waboot è contenuto nella cartella `/inc/core`.
- `/inc/core/alert`: contiene classi che implementano un sistema di alert. E' possibile usare queste classi tramite la facade `Waboot\inc\core\facades\Alert`.
- `/inc/core/cli`: contiene classi che implementano i comandi di wp-cli.
- `/inc/core/facades`: implementa delle facade per le classi del core.
- `/inc/core/helpers`: contiene classi helper.
- `/inc/core/mail`: contiene classi helper per gestire l'invio di mail.
- `/inc/core/multilanguage`: contiene classi helper per gestire i multi-lingua.
- `/inc/core/mvc`: contiene classi per implementare il paradigma MVC.
- `/inc/core/repositories`: contiene classi helper per implementare i repository.
- `/inc/core/utils`: contiene classi helper.
- `/inc/core/woocommerce`: contiene classi helper per WooCommerce.
- `/inc/core/AssetsManager.php`: classe principale per la gestione degli assets.
- `/inc/core/DB.php`: classe principale per la gestione delle query.

## 📝 Standard di Codifica

### JavaScript
- Usa la sintassi ES6+ (arrow functions, destructuring).
- Documenta ogni funzione complessa con JSDoc:
  ```javascript
  /**
   * Descrizione breve
   * @param {type} name - Descrizione
   * @returns {type}
   */
- Parti dal file `assets/src/js/main.js`. Questo è il file da cui parte la compilazione (`npm run assets:build`). La compilazione viene eseguita tramite gulp, usando il `gulpfile.js`.
- Usa jQuery integrata dentro WordPress

### PHP
- Usa la sintassi PHP 8.3. Specifica sempre il return type delle funzioni e il type degli argomenti.
- Documenta ogni funzione complessa con PHPDoc.
- Se devi creare un hook di WordPress (una azione o un filtro) usa il metodo `add_filter` o `add_action` con una funzione anonima.
- Per gli array, usa le parentesi quadre.
- Usa il camelCase per funzioni e nomi delle variabili.
- Non anteporre mai nessun prefisso dedicato al tema o al progetto ai nomi delle funzioni o delle classi. Usa i namespace e la struttura delle cartelle per separare funzionalità dedicate.
- Ogni file all'interno della cartella `inc` deve contenere un namespace coerente.
- Classi e trait devono essere scritte in PascalCase e non ci deve essere più di una classe o trait per file. Il file deve chiamarsi come la classe.
- Tieni in massima considerazione le prestazioni, sopratutto per le query SQL.
- Hook per funzionalità semplici e poco complesse possono essere creati direttamente nel file `inc/hooks/hooks.php`.
- Hook per funzionalità più complesse (che richiedono più hook) devono essere creati in un file separato nella cartella `inc/hooks`. Questo file va poi incluso nel file `functions.php`.
- Se devi usare delle stringhe come valori di variabili, considera di mapparle in un enum in modo da non ripetere le stesse stringhe in più parti del codice.
- Se devi creare un comando CLI, estendi `waboot/inc/core/cli/AbstractCommand.php`
- Se devi creare un comando CLI che gestisce un CSV, estendi `waboot/inc/core/cli/AbstractCSVParserCommand.php`
- Registra i comandi CLI nel file `inc/cli.php` usando la funzione `registerCommand` nel file `waboot/inc/core/helpers/cli.php`
- Se devi fare delle query a database, dai priorità all'uso della classe `/inc/core/DB.php`. Puoi usare questa classe tramite la facade `Waboot\inc\core\facades\Query`. Questa classe implementa `illuminate/database`. Un esempio di uso di questa classe è in `inc/core/woocommerce/addresses/ShippingAddressRepository.php`.
- Se devi registrare degli assets per il frontend, dai priorità all'uso della classe `/inc/core/AssetsManager.php`. Un esempio di uso di questa classe è in `inc/hooks/assets.php`.