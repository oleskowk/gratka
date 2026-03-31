Zaczynam od dodania globalnego AGENTS.md który będzie zawierał ogólne zasady dla AI agentów pracujących nad projektem.

Zaczynam od aplikacji symfony zgodnie z instrukcjami.
- W entrypoincie aplikacji php uruchamiany composer update, który jest niepotrzebny i niebezpieczny. Zamiast tego należy używać composer install by polegać na composer.lock.
- Produkcyjny obraz instaluje deweloperskie zależności i nie optymaloizuje autoloadera - poprawiam.
- Tworzę nową warstwę "base" w dockerfile, by uniknąć rozjazdu środowisk, oraz przyspieszyć budowanie obrazów.
- Dodaję plik Makefile, by ułatwić uruchamianie aplikacji.