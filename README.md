# TaskleiQ

TaskleiQ to zaawansowana aplikacja do zarządzania zadaniami i projektami, umożliwiająca tworzenie projektów, dodawanie zadań oraz przypisywanie użytkowników do poszczególnych zadań. System oferuje także zaawansowane funkcje administracyjne, takie jak zarządzanie użytkownikami, autoryzacja administratora oraz zapraszanie użytkowników do grupy za pomocą tokenów.

## Funkcje

- **Zarządzanie projektami**: Tworzenie, edytowanie i usuwanie projektów.
- **Zarządzanie zadaniami**: Dodawanie, edytowanie i usuwanie zadań, przypisywanie ich do projektów.
- **Przypisywanie użytkowników**: Przypisywanie użytkowników do konkretnych zadań w ramach projektów.
- **Zapraszanie do grupy**: Możliwość zapraszania nowych użytkowników do grupy za pomocą tokenów zaproszeń.
- **Autoryzacja administratora**: Panel administracyjny z funkcjami dodawania, usuwania oraz zarządzania użytkownikami.
- **Responsywny interfejs**: Nowoczesny i responsywny interfejs użytkownika oparty na Bootstrap 5.3 i Vue.js.
- **Bezpieczna autoryzacja**: System autoryzacji użytkowników z wykorzystaniem PHP i MySQL.

## Technologie

- **Backend**: PHP (OOP), MySQL, architektura MVC
- **Frontend**: Bootstrap 5.3, Vue.js
- **Baza danych**: MySQL

## Instalacja

### 1. Klonowanie repozytorium

```bash
git clone https://github.com/twoj-uzytkownik/taskleiq.git
cd taskleiq
```
### 2. Konfiguracja serwera

1. Skonfiguruj środowisko PHP na swoim serwerze (wymagane PHP 7.4 lub nowsze).
2. Utwórz bazę danych MySQL i zaimportuj do niej plik `database.sql` z folderu projektu.
3. Skonfiguruj plik `config.php`, w którym ustawisz dane dostępowe do bazy danych.

### 3. Instalacja zależności

Aplikacja nie wymaga dodatkowych narzędzi do instalacji. Wystarczy mieć zainstalowane PHP i MySQL.

### 4. Uruchomienie aplikacji

Uruchom aplikację na swoim lokalnym serwerze lub zewnętrznym serwerze Apache/Nginx. Przykład użycia XAMPP:

1. Skopiuj projekt do folderu `htdocs`.
2. Uruchom serwer Apache i MySQL.
3. Otwórz przeglądarkę i przejdź pod adres `http://localhost/taskleiq`.

## Użycie

1. **Logowanie**: Zaloguj się za pomocą swojego konta.
2. **Projekty**: Utwórz nowy projekt, dodaj zadania i przypisz użytkowników.
3. **Panel administratora**: Zaloguj się jako administrator, aby zarządzać użytkownikami oraz przydzielać role.
4. **Zaproszenia**: Generuj tokeny zaproszeń, aby zaprosić nowych użytkowników do projektu.

## Licencja

TaskleiQ jest udostępniany na licencji [MIT](LICENSE).
