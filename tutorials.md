WABOOT STARTER THEME: Tutorials!
====================

< Readme here >

Utilizzare la Vagrant Machine
----------------

GIT URL: https://bitbucket.org/waga/vagrantserver.git

- Installare VirtualBox e Vagrant scaricando gli installer dai rispettivi siti.
- La macchina Vagrant si basa sulla box `hashicorp/precise64`; questa deve essere scaricata di avviare la macchina, quindi tramite terminale dare il comando:

        vagrant box add hashicorp/precise64
        
    (se chiede di scegliere la piattaforma, selezionare VirtualBox)

- Creare (se non esiste) una directory che sarà la root di tutti i progetti in locale, per esempio: `/<utente>/htdocs` , `c:\localhost\public_html`, ecc...
- Creare una cartella specifica per la Vagrant Machine, relativamente vicina a quella creata precedentemente, per esempio: `/<utente>/vagrant` , `c:\localhost\vagrant`
- Posizionarsi in quest'ultima cartella e clonare il repository GIT della macchina (il comando per il terminale è visualizzabile su bitbucket, cliccando su "Clone"):

    esempio:
    
        git clone https://<nome utente>@bitbucket.org/waga/vagrantserver.git

- Ottenuta la macchina, copiare (non rinominare) il file `vagrant_assets/config-sample.yaml` in `vagrant_assets/config.yaml`, aprirlo e configurarlo:

`username`:

riservato per usi futuri

`mac`:

inserire un mac address generato da: `http://www.miniwebtool.com/mac-address-generator/` o altro generatore, il formato deve essere 0010FA6E384A

`ip`:

inserire un ip disponibile nella propria rete locale. Per capire che tipo di indirizzi (192.168.xxx.xxx, 10.0.xxx.xxx, ...) assegna il router della lan è possibile usare i comandi `ifconfig (osx\unix) o `ipconfig` (windows), oppure entrare nell'amministrazione del router.

`name`:

il nome della Virtual Machine

`synced_folder - source`:

il path alla root dei progetti relativamente alla directory in cui è presente il Vagrantfile. Secondo gli esempi di prima sarebbe: `../htdocs` oppure `../public_html`

`synced_folder - target`:

non modificare (è il path per la documentroot di apache sulla virtual machine)

`synced_folder - nfs`:

un file system per la gestione delle cartelle condivise disponibile su sistemi unix. Ancora non funziona bene, quindi lasciare su false.

- Dare il comando `vagrant up` dalla directory dove si trova il `Vagrantfile`: la macchina si inizializzerà e installerà tutto il necessario. Potrebbe chiedere a quale dispositivo di rete attaccarsi (scegliere quello che si utilizza per gestire la lan tra i pc o con il router - di solito Airport per i mac).

Finita l'installazione sarà possibile andare via browser al numero ip specificato in configurazione. PHPMYADMIN sarà disponibile a: `http://<numeroIP>/phpmyadmin`

I comandi per gestire la VM sono disponibili qui: `http://docs.vagrantup.com/v2/cli/index.html` . E' possibile "spegnerla" e "accenderla" anche via GUI di Virtualbox.

Riferirsi al readme sul repository della VM per approfondimenti.

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

    npm install -g grunt-cli
    
    npm install -g bower

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





