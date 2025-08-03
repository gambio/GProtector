# 🛡️ Gambio GProtector

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Gambio Core Modul](https://img.shields.io/badge/Gambio-Core_Module-green.svg)](#)
[![Security Protection](https://img.shields.io/badge/Security-Request_Filtering-critical.svg)](#)

## 🔒 Übersicht
**GProtector** ist ein integraler Bestandteil des Gambio-Core und schützt den Shop vor Angriffen, indem es **alle eingehenden POST- und GET-Parameter** analysiert und filtert. Dadurch werden potenzielle Sicherheitslücken und Exploits abgefangen, bevor sie Schaden anrichten können.

Das Modul agiert als **Sicherheits-Firewall auf Anwendungsebene** und ist speziell darauf ausgelegt, auch unbekannte Angriffsvektoren proaktiv zu blockieren.

---

## 🛠️ Features
- Filterung und Validierung sämtlicher **POST- & GET-Anfragen**
- Schutz vor **SQL-Injections**, **XSS-Angriffen**, **Code Injections** und anderen Exploits
- Automatische Blockierung verdächtiger oder manipulierten Parameter
- Updatefähig über Gambio-Core-Updates (kein separates Modul-Update nötig)
- Performance-optimiert: Minimaler Einfluss auf Ladezeiten
- Keine Konfiguration erforderlich — **“Plug & Protect”**

---

## 🚀 Installation
**GProtector ist standardmäßig im Gambio-Core ab Version GX 4.x enthalten.**

Für Entwickler/Shopbetreiber:
1. Sicherstellen, dass der Shop auf einer aktuellen Gambio-Version (mind. GX 4.x) läuft.
2. Keine separate Installation notwendig – GProtector ist direkt im Core aktiv.
3. Bei Bedarf kann das Logging von geblockten Anfragen im Debug-Modus überprüft werden.

---

## 📋 Kompatibilität
- Gambio GX 4.0.x bis aktuellste Version
- PHP 7.4 – 8.x

---

## ❗ Hinweise
- GProtector ersetzt **keine serverseitige Firewall (WAF)**, bietet aber einen zusätzlichen Schutz auf Anwendungsebene.
- Für vollständige Sicherheit sollten auch andere Maßnahmen wie regelmäßige Updates, sichere Passwörter und serverseitige Absicherungen erfolgen.
- Es werden bewusst restriktive Filter eingesetzt – sollte es in Ausnahmefällen zu False Positives kommen, kann dies über den Gambio Support geklärt werden.

---

## 🆘 Support
Da GProtector Teil des Gambio-Core ist, erfolgen Support & Sicherheitsupdates direkt durch Gambio:  
👉 [Gambio Support Portal](https://www.gambio-support.de)

---

## 📄 Lizenz
Dieses Modul unterliegt der **GPL 2.0 Lizenz**.  
Weitere Informationen: [GPL-2.0 License](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)

---

## 🔐 Sicherheit durch Design
GProtector wird kontinuierlich weiterentwickelt, um neue Angriffsvektoren und Sicherheitslücken proaktiv abzuwehren — **damit dein Onlineshop sicher bleibt.**

