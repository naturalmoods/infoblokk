# Infoblokk

Egyszerű WordPress plugin fix infoblokkok megjelenítésére.

## Funkciók

- több infoblokk kezelése egy oldalon
- infoblokkonként külön URL
- felső vagy alsó pozíció
- Széchenyi Terv Plusz bal vagy jobb oldali megjelenítése; a többi kép mindig jobb oldalon jelenik meg
- választható Széchenyi Terv Plusz, ERFA, ESZA, KA, ESBA kép
- opcionális bezárás gomb
- bezárás megjegyzése böngésző session alatt

## Telepítés

1. Másold a mappát a WordPress `wp-content/plugins/infoblokk` könyvtárába.
2. Aktiváld az **Infoblokk** plugint a WordPress adminban.
3. Állítsd be itt: **Beállítások → Infoblokk**.

## Fejlesztés

PHP szintaxis ellenőrzés:

```bash
php -l infoblokk.php
```

## Fájlok

```text
infoblokk.php
img/
  szechenyi-terv-plusz.webp
  infoblokk_ERFA_also.webp
  infoblokk_ERFA_felso.webp
  infoblokk_ESBA_also.webp
  infoblokk_ESBA_felso.webp
  infoblokk_ESZA_also.webp
  infoblokk_ESZA_felso.webp
  infoblokk_KA_also.webp
  infoblokk_KA_felso.webp
```
