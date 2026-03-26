 /*******************************************************************************
  Leitura e gravacao de dados usando o Kit RFID MFRC522 (v2.0)

  Codigo de exemplo para leitura e gravacao de dados em uma tag

  Copyright 2026 RoboCore.
  Escrito por Carlos Daniel (09/02/2026).

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version (<https://www.gnu.org/licenses/>).
*******************************************************************************/

#include <SPI.h>
#include <MFRC522v2.h>
#include <MFRC522DriverSPI.h>
#include <MFRC522DriverPinSimple.h>
#include <MFRC522Debug.h>

const int PINO_SDA = 10;

MFRC522DriverPinSimple ssPin(PINO_SDA);
MFRC522DriverSPI driver(ssPin, SPI);
MFRC522 mfrc522(driver);

enum EstadoOperacao {
  MODO_ESPERA,
  MODO_LEITURA,
  MODO_GRAVACAO
};
EstadoOperacao estadoAtual = MODO_ESPERA;

MFRC522::MIFARE_Key chave;

byte bloco = 4;
byte buffer[18];
byte tamanho = 18;
byte texto[16];

const byte AUTH_KEY_A = 0x60;

const byte MAX_TENTATIVAS = 25;
const unsigned long TIMEOUT_MS = 2500;
const unsigned long PAUSA_MS = 15;

bool tagSelecionada = false;
bool autenticado = false;
bool leituraOK = false;
bool gravacaoOK = false;

void setup() {
  Serial.begin(9600);
  SPI.begin();
  mfrc522.PCD_Init();

  for (byte i = 0; i < 6; i++) {
    chave.keyByte[i] = 0xFF;
  }
}

void loop() {
  if (Serial.available() > 0) {
    String req = Serial.readStringUntil('\n');
    req.trim();

    if (req.startsWith("<") && req.endsWith(">")) {
      String cmdBody = req.substring(1, req.length() - 1);

      if (cmdBody == "READ") {
        estadoAtual = MODO_LEITURA;
        leitura();
        estadoAtual = MODO_ESPERA;
      }
      else if (cmdBody.startsWith("WRITE:")) {
        estadoAtual = MODO_GRAVACAO;
        String payload = cmdBody.substring(6);

        memset(texto, ' ', 16);
        for(int i = 0; i < payload.length() && i < 16; i++) {
          texto[i] = payload[i];
        }

        gravacao();
        estadoAtual = MODO_ESPERA;
      }
      else {
        responder("ERR:INVALID_CMD");
      }
    }
  }
}

void responder(String mensagem) {
  Serial.print("<RES:");
  Serial.print(mensagem);
  Serial.print(":FW:");
  byte v = driver.PCD_ReadRegister(MFRC522Constants::VersionReg);
  Serial.print(v, HEX);
  Serial.println(">");
}

void leitura() {
  esperarTagESelecionar();
  if (!tagSelecionada) {
    responder("ERR:NO_TAG");
    return;
  }

  autenticarBloco();
  if (!autenticado) {
    responder("ERR:AUTH");
    finalizaOperacao();
    return;
  }

  byte dados[16];
  lerBloco16(dados);

  if (!leituraOK) {
    responder("ERR:READ_FAILED");
    finalizaOperacao();
    return;
  }

  String payload = "OK:";
  for (byte i = 0; i < 16; i++) {
    if (dados[i] >= 32 && dados[i] <= 126) {
      payload += (char)dados[i];
    } else {
      payload += " ";
    }
  }

  responder(payload);
  finalizaOperacao();
}

void gravacao() {
  esperarTagESelecionar();
  if (!tagSelecionada) {
    responder("ERR:NO_TAG");
    return;
  }

  gravarBloco16Verificado(texto);

  if (gravacaoOK) {
    responder("OK:WROTE");
  } else {
    responder("ERR:WRITE_FAILED");
  }

  finalizaOperacao();
}

void limpaEstado() {
  mfrc522.PCD_StopCrypto1();
  delay(5);
}

void finalizaOperacao() {
  mfrc522.PCD_StopCrypto1();
  mfrc522.PICC_HaltA();
  delay(5);
}

bool igual16(const byte a[16], const byte b[16]) {
  for (byte i = 0; i < 16; i++) {
    if (a[i] != b[i]) {
      return false;
    }
  }
  return true;
}

void esperarTagESelecionar() {
  tagSelecionada = false;
  unsigned long inicio = millis();

  while (millis() - inicio < TIMEOUT_MS) {
    for (byte t = 0; t < MAX_TENTATIVAS; t++) {
      byte atqa[2];
      byte atqaSize = sizeof(atqa);

      bool detectou =
        mfrc522.PICC_IsNewCardPresent() || (mfrc522.PICC_RequestA(atqa, &atqaSize) == 0) || (mfrc522.PICC_WakeupA(atqa, &atqaSize) == 0);

      if (detectou && mfrc522.PICC_ReadCardSerial()) {
        tagSelecionada = true;
        return;
      }

      limpaEstado();
      delay(PAUSA_MS);
    }
    delay(20);
  }
}

void autenticarBloco() {
  autenticado = false;

  for (byte t = 0; t < MAX_TENTATIVAS; t++) {
    byte status = mfrc522.PCD_Authenticate(AUTH_KEY_A, bloco, &chave, &(mfrc522.uid));
    if (status == 0) {
      autenticado = true;
      return;
    }

    limpaEstado();
    delay(PAUSA_MS);

    esperarTagESelecionar();
    if (!tagSelecionada) {
      return;
    }
  }
}

void lerBloco16(byte saida[16]) {
  leituraOK = false;

  for (byte t = 0; t < MAX_TENTATIVAS; t++) {
    tamanho = 18;
    byte status = mfrc522.MIFARE_Read(bloco, buffer, &tamanho);

    if (status == 0) {
      for (byte i = 0; i < 16; i++) {
        saida[i] = buffer[i];
      }
      leituraOK = true;
      return;
    }

    limpaEstado();
    delay(PAUSA_MS);

    esperarTagESelecionar();
    if (!tagSelecionada) {
      return;
    }

    autenticarBloco();
    if (!autenticado) {
      return;
    }
  }
}

void gravarBloco16Verificado(const byte entrada[16]) {
  gravacaoOK = false;

  for (byte t = 0; t < MAX_TENTATIVAS; t++) {
    autenticarBloco();
    if (!autenticado) {
      limpaEstado();
      delay(PAUSA_MS);

      esperarTagESelecionar();
      if (!tagSelecionada) {
        return;
      }

      continue;
    }

    mfrc522.MIFARE_Write(bloco, (byte*)entrada, 16);

    byte ver[16];
    lerBloco16(ver);

    if (leituraOK && igual16(ver, entrada)) {
      gravacaoOK = true;
      return;
    }

    limpaEstado();
    delay(PAUSA_MS);

    esperarTagESelecionar();
    if (!tagSelecionada) {
      return;
    }
  }
}
    