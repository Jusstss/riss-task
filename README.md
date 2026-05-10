## 1. Algoritmo numeris ir pavadinimas

**Algoritmas:** SHA-256  
**Darba atliko:** Justas Šmulkštys

---

## 2. Įgyvendinta ir neįgyvendinta

### Įgyvendinta
- SHA-256 maišos algoritmas
- Failo skaitymas baitais (ne kaip tekstas)
- Maišos reikšmės išvedimas hex formatu į ekraną
- Failo pavadinimo perdavimas komandinės eilutės parametru
- Klaidos apdorojimas (neegzistuojantis failas, netinkami parametrai)
- Komentarai

### Neįgyvendinta
- Didelių failų maišos reikšmės skaičiavimas (skaitomas visas failas vienu metu dėl ko neužtenka atminties didesniems failams, reikėtų skaityt chunk-by-chunk)

---

## 3. Paleidimo instrukcija

### Paleidimas
```bash
./setup.sh // jei nėra suintstaliuotas php
php riss_task.php <įvesties_failas>
```

### Parametrai
| Parametras          | Aprašymas                                |
| ------------------- | ---------------------------------------- |
| `<įvesties_failas>` | Kelias iki failo, kurio maišą skaičiuoti |

### Pavyzdžiai
```bash
php riss_task.php petras.txt

# Jei failas neperduodamas, programa paprašys jo įvesti
php riss_task.php
```

### Rezultatas
```
SHA-256: 2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824
```

---

## 4. Laiko sąnaudos

| Etapas                | Laikas (val.) |
| --------------------- | ------------- |
| Literatūros skaitymas | 1h            |
| Video analizavimas    | 1h            |
| Programavimas         | 3-4h          |
| Ataskaitos ruošimas   | 1h            |
| **Viso**              | 6-7h          |

---

## 5. Iššūkiai ir sunkumai

- Sunku buvo suprasti patį algoritmą vien iš specifikacijos, manę išgelbėjo rastas video, kuris pažingsniui su pavyzdžiu praeiną pro visus veiksmus.
- Darbas su bitais/bytais išpradžių buvo gan sudetingas, kol neatradau `decbin()`, kas padėjo daug lengviau įsigilinti ir _debugginti_.
- `rotr()` implementavimas, kadangi iš pat pradžių skaitydamas dokumentacija tikėjausi, kad bus kokia nors funkcija kaip pvz. `>>`, `&` tačiau paskui supratau, kad reikės pačiam rašyti.
---

## 6. Informacijos šaltiniai

1. NIST FIPS PUB 180-4 — *Secure Hash Standard (SHS)*, 2015.  
   https://nvlpubs.nist.gov/nistpubs/FIPS/NIST.FIPS.180-4.pdf

2. [SHA-256 | COMPLETE Step-By-Step Explanation (W/ Example)](https://www.youtube.com/watch?v=orIgy2MjqrA)