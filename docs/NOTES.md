Zaczynam od dodania globalnego AGENTS.md który będzie zawierał ogólne zasady dla AI agentów pracujących nad projektem.

Zaczynam od aplikacji symfony zgodnie z instrukcjami.
- W entrypoincie aplikacji php uruchamiany composer update, który jest niepotrzebny i niebezpieczny. Zamiast tego należy używać composer install by polegać na composer.lock.
- Produkcyjny obraz instaluje deweloperskie zależności i nie optymaloizuje autoloadera - poprawiam.
- Tworzę nową warstwę "base" w dockerfile, by uniknąć rozjazdu środowisk, oraz przyspieszyć budowanie obrazów.
- Dodaję plik Makefile, by ułatwić uruchamianie aplikacji.
- Ekstrahuje konfigurację do zmiennych środowiskowych i dodaję plik .env.dist
- Po uruchomieniu aplikacji, włączam ją, przeglądam kod, testuję, próbuję zrozumieć jak ma działać i co robić.
- Fikstury są trudne do utrzymywania w takiej formie(jeden plik w którym mamy wszystko, hardkodowane dane, brak możliwości wygenerowania większego zbioru danych pod testy wydajnościowe), warto by się pochylić nad alternatywnym podejściem - wykorzystanie sprawdzonych bibliotek. Dodatkowo ich problemem jest to, że nei sprawdzają czy dane istnieją, co może prowadzić do błędów.
- Katalog "Likes" wygląda na nieudolną próbę wydzielenia bounded contextu, ale jest niekompletny i nie spójny.
- Dostrzegam pewne problemy jak ręczne tworzenie serwisów, zamiast korzystania z DI, kontroler nie stosuje się do SRP, repozytorium nie powinno znać encji usera, ręczne operowanie na sesji zamiast używać Symfony Security, brak pełnego silnego typowania w kodzie, podatność na SQLInjection w AuthController, brak sprawdzenia  czy token należy do usera, logowanie przez GET jest niebezpieczne - token zapisze się w historii
- Tworzę testy funkcjonalne, by refaktoryzacja była bezpieczna. testy funkcjonalne można by opakowywać w bazodanowe transakcje, by nie zaśmiecać bazy danymi testowymi. Można by je napisać też w gherkin by były czytelniejsze. Staram się by były możliwie mało powiązane z implementacją.