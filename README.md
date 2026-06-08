<div align="center">
  <img src="https://img.shields.io/badge/Projeto_Acadêmico-Monitoramento_Pecuário-blue?style=for-the-badge&logo=googlescholar&logoColor=white" alt="Logo" width="300" />

  # 🐄 Estudo e Implementação de Rastreamento de Gado com Arduino e RFID

  *Este projeto consiste em um protótipo funcional desenvolvido para fins acadêmicos, explorando a integração entre hardware de baixo custo, aplicações desktop e sistemas de gestão web para o monitoramento pecuário.*

  [![Java](https://img.shields.io/badge/Java-21-ED8B00?style=for-the-badge&logo=openjdk&logoColor=white)](https://java.com)
  [![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![Arduino](https://img.shields.io/badge/Arduino-C++-00979D?style=for-the-badge&logo=arduino&logoColor=white)](https://arduino.cc)
</div>

---

## 🎓 Contextualização do Projeto

Este software foi desenvolvido como parte de um estudo sobre a automação na pecuária. O objetivo central é demonstrar a viabilidade técnica de um sistema de identificação animal utilizando a tecnologia **RFID (Identification by Radio Frequency)**, integrada a uma arquitetura de software multicamada (IoT, Desktop e Nuvem).

O projeto aborda os desafios de sincronização de dados em tempo real e a integridade da informação em ambientes de campo, servindo como uma prova de conceito para sistemas de manejo inteligente.

---

## 🛠️ Objetivos Técnicos

- **Automação de Coleta:** Implementar a captura de identificadores únicos via hardware (MFRC522) eliminando a transcrição manual.
- **Interoperabilidade:** Estabelecer uma comunicação estável entre o firmware (C++), o software de controle (Java) e o servidor de dados (PHP/Laravel).
- **Consistência de Dados:** Aplicar regras de validação rigorosas para garantir que as informações coletadas no campo reflitam com precisão no banco de dados central.
- **Interface de Monitoramento:** Desenvolver um dashboard para visualização de métricas sanitárias e histórico de vacinação.

---

## 🧩 Estrutura do Ecossistema

O sistema é composto por três módulos integrados:

### 1. Camada de Aquisição (`modulo_arduino`)
Firmware responsável pela interface física com o sensor RFID. Gerencia a modulação de rádio e a transmissão dos dados via protocolo Serial.

### 2. Camada de Aplicação (`modulo_desktop`)
Desenvolvida em **Java 21**, atua como o terminal de campo. Processa os sinais seriais e realiza a interface com o usuário e com o back-end através de requisições REST.

### 3. Camada de Gestão e Persistência (`modulo_web`)
Utiliza o framework **Laravel 12** para fornecer uma API segura e um painel de controle web. É o núcleo onde as regras de negócio e a persistência de longo prazo residem.

---

## � Metodologia de Desenvolvimento

O desenvolvimento seguiu princípios de engenharia de software acadêmica, incluindo:
- **Modularização:** Separação clara de responsabilidades entre hardware e software.
- **TDD (Test Driven Development):** Implementação de testes unitários e de integração em ambas as plataformas de software (JUnit e PHPUnit).

---

---

## 🔗 Referências e Inspiração

A implementação da camada de hardware e os conceitos base de comunicação com o módulo MFRC522 foram inspirados e fundamentados no tutorial de [Leitura e Escrita com RFID Mifare MFRC522](https://www.robocore.net/tutoriais/leitura-escrita-com-rfid-mifare-mfrc522) da **RoboCore**.

> **Observação:** Este projeto possui caráter estritamente educativo e experimental. Para detalhes de execução e requisitos técnicos, consulte o [README Técnico](TECHNICAL_README.md).

