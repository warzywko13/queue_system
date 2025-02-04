# Aplikacja monitorowania kolejki górskiej

## 1. Konfiguracja środowiska
Należy utworzyć plik .env

W pliku tym należy ustawić środowisko:

- Produkcyjne:
```
CI_ENVIRONMENT = production
```

- Developerskie:
```
CI_ENVIRONMENT = development
```

## 2. Uruchomienie monitora
W terminalu należy uruchmić polecenie:

```
php spark app:monitor_coasters
```

## 3. API

### Dodawanie kolejek

```
[POST] {host}/api/coasters
```

Przykładowy request:
``` json
{
    "staff_count": 12,
    "customer_count": 200, 
    "track_length": 1800, 
    "hours_from": "8:00", 
    "hours_to": "16:00"
}
```

Przykładowy response:
``` json
{
    "status": "ok",
    "message": "Coaster 1 added successfully"
}
```

### Aktualizacja kolejki

```
[PUT] {host}/api/coasters/1
```

Przykładowy request:
``` json
{
    "staff_count": 12,
    "customer_count": 200, 
    "track_length": 1800, 
    "hours_from": "8:00", 
    "hours_to": "16:00"
}
```

Przykładowy response:
``` json
{
    "status": "ok",
    "message": "Coaster 1 updated successfully"
}
```

### Dodawanie wagonów

```
[POST] {host}/api/coasters/1/wagons
```

Przykładowy request:
``` json
{
    "seat_count": 32,
    "speed": 1.2
}
```

Przykładowy response:
``` json
{
    "status": "ok",
    "message": "Wagon 1 added successfully"
}
```

### Usuwanie wagonu

```
[DELETE] {host}/api/coasters/1/wagons/4
```

Przykładowy response:
``` json
{
    "status": "ok",
    "message": "Wagon 1 removed successfully"
}
```