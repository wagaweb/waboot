WABOOT STARTER THEME: Tutorials!
====================

< Readme here >

Utilizzare Grunt
----------------

**Versione breve**

- Installare nodejs: http://nodejs.org/
- Recarsi (da terminale) nella directory dove si è clonato il repository del tema

    - **Solo la prima volta** - dare i comandi con privilegi di amministratore:
    
        - `npm install -g grunt-cli`
        - `npm install -g bower`
        - `npm install`
        - `grunt setup`
        - Prima di iniziare a lavorare: `grunt watch`
    - **Volte successive** - Prima di iniziare a lavorare: `grunt watch`

**Versione dettagliata**

**Grunt** è un *task-manager* basato su **nodejs**; si interfaccia quindi con altri tool (per esempio Bower, il compilatore di less, funzioni di sistema...) eseguendoli automaticamente con il set di istruzioni specificate all'interno del file `Gruntfile.json`. 

Attualmente contiene i task per:
- compilare i file less
- scaricare (tramite Bower) le versioni corrette di bootstrap, fontawesome e tutti gli script "vendor" utilizzati del tema
- copiare specifici file dai pacchetti scaricati con Bower (per esempio: bootstrap.min.css) nella posizione corretta all'interno della folder structure del tema.

**1]** Per utilizzare Grunt è necessario che sul sistema sia installato nodejs: l'installer per le varie piattaforme è disponibile qui: http://nodejs.org/

**2]** Una volta installato nodejs si possono iniziare ad utilizzare i comandi della piattaforma dal terminale del proprio sistema. 

Per prima cosa bisogna installare l'interfaccia comandi di Grunt attraverso il gestore pacchetti di node (*npm*) e renderla disponibile all'intero sistema. Può essere utile installare anche Bower in questo modo. 

Dare quindi i seguenti comandi da terminale (assicurandosi di avere i privilegi di admin, per esempio su linux bisogna precedere ogni comando con `sudo`):

    $ npm install -g grunt-cli
    
    $ npm install -g bower

Il `-g` installa globalmente il pacchetto; se omesso, il pacchetto viene installato nella directory corrente (e non sono necessariamente richiesti i privilegi di admin).

**3]** Adesso ci si può recare tramite terminale nella directory del tema (dove risiede anche il Gruntfile) e utilizzare i seguenti comandi:

    npm install

Installa nella cartella `node_modules` i pacchetti di node necessari al funzionamento dei task specificati nel Gruntfile ed eventualmente di altre features.

    Grunt setup
    
Esegue un `bower install` (quindi viengono scaricati i pacchetti specificati nel file `bower.json` in `bower_modules`), copia alcuni file da `bower_modules` nelle posizioni corrette all'interno delle cartelle del tema e compila i file less.

A questo punto dovreste avere il necessario per lavorare: controllate che in `/sources` ci siano i file di bootstrap e che sia stato ceato il file `style.css` in `/assets/css`.

**4]** Da questo momento è possibile utilizzare i comandi:

    Grunt watch

Controlla live i cambiamenti ai file e compila i file less appena vengono modificati. Potrà essere potenziato con altre funzioni di controllo.

    Grunt less:dev
    
Compila i file less in modalità non minimizzata.

    Grunt recess:production
    
Compila i file less in modalità produzione utilizzando Twitter Recess (ispirato al tema root)

    Grunt build
    
Effettua tutte le operazioni per creare una release "da produzione" del tema (attualmente minimizza e compila less)





