#NoEnv  ; Recommended for performance and compatibility with future AutoHotkey releases.
SendMode Input  ; Recommended for new scripts due to its superior speed and reliability.
SetWorkingDir %A_ScriptDir%  ; Ensures a consistent starting directory.
SetBatchLines -1
#SingleInstance, force
#Persistent

; SET ICON
;@Ahk2Exe-SetMainIcon Icons\build.ico

#Include, Libs\WatchFolder.ahk
#Include, Libs\Socket.ahk

Directory := ""     ; User selected Directory
Port      := 1337   ; Default Socket Port
Data      := []     ; Data to send
Activity  := 0      ; Something was modified
Host      := "0.0.0.0"
hostname  := "localhost"

Initializer()

Initializer() {
  global

  ; Check if file exist
  TempFile := A_Temp "\express_reloader_cfgs.txt"
  if (FileExist(TempFile)) {
    FileRead, fileContent, %TempFile%
    tmpAr     := StrSplit(fileContent, ";")
    Port      := tmpAr[1]
    Directory := tmpAr[2]
  } else {
    MsgBox, 4128, BIENVENUE, Bienveue encore %A_UserName%`r`n`r`nVous allez être invité à sélectionner un dossier a observer.`r`n`r`nEXPRESS OBSERVER`r`nVous permet d'observer et dactualiser votre page web automatiquement sans passer par le bouton de Recharge.`r`nRassurez-vous que le fichier JS est déjà importé dans votre projet.`r`n`r`nUtiliser seuelement en Developpement, 30
    FileSelectFolder, SelectedFolder,, 1, Comme c'est la premiere fois vous devez selection un dossier a observer.
    if (StrLen(SelectedFolder) > 1) 
    {
      file := FileOpen(TempFile, "w", "utf-8")
      file.Write(Port ";" SelectedFolder)
      file.Close()
    } else {
      MsgBox, 16, Procedure annuler, Comme vous venez d'annuler.`r`nle programme va cesser la configuration pour le moment et va s'arrêter., 30
      ExitApp, 1
    }
  }
}

; Menu, Tray, Icon, Shell32.dll, 174
Menu, Tray, Add , EXPRESS OBSERVER v1.0, About
Menu, Tray, Add , %Hostname% : %Port%, PortMan
Menu, Tray, Add , Changer de repertoir, changeFold
Menu, Tray, Add
Menu, Tray, Add, Relancer l'observateur, RestartApp
Menu, Tray, Add, Arreter l'observateur, StopApp
Menu, Tray, NoStandard
Menu, Tray, MainWindow

PortMan() {
  global

  MsgBox, 4148, Port de contact, EXPRESS RELOADER`r`n`r`nutilise un serveur TCP Socket intégrer pour communiquer avec son homologue (script) qui est dans votre project (page html) pour recharger votre page.`r`n`r`nNom du hote : %hostname%`r`nPort                 : %Port%`r`n`r`nRepertoir Observer :`r`n%Directory%`r`n`r`nVoulez-vous modifier le port de communication ?`r`n`r`n >= 80+, 30
  IfMsgBox, Yes
  {
    InputBox, inutedValue, Nouveau PORT, Entrer un nouveau PORT de COM.`r`nLe port servira à la communication des données.`r`nLa valeur du port ne doit pas être inférieure de 80 et supérieure à 9999.`r`n`r`nExpress communique sur le réseau local de la machine (localhost).,, 300, 200,,, local,, %Port%
    if (inutedValue >= 80 && inutedValue <= 9999) {
      file := FileOpen(TempFile, "w", "utf-8")
      file.Write(inutedValue ";" Directory)
      file.Close()
      RestartApp()
    } else {
      msgbox Vous avez mis une valeur non conforme.
    }
  }
  RestartApp()
}

changeFold() {
  global

  FileSelectFolder, SelectedFolder,, 1, Slectionner une autre repertoir a observer.
  if (StrLen(SelectedFolder) > 1) 
  {
    file := FileOpen(TempFile, "w", "utf-8")
    file.Write(Port ";" SelectedFolder)
    file.Close()
    RestartApp()
  }
  RestartApp()
}

About() {
  global

  MsgBox, 36, À propos, EXPRESS RELOADER`r`n`r`nEst un outil développé par Congo Cloud Computer.`r`n`r`nune solution pour permettre à ses développeurs de profiter et d'optimiser encore un peu plus leurs temps de productives.`r`n`r`n@2023 3c-numeric.com v1.0 `r`nVoulez-vous visiter le site de 3C ?, 30
  IfMsgBox, Yes
  {
    Run, https://3c-nimeric.com
  }
  RestartApp()
}

StopApp(status := 0) {
  ExitApp, %status%
}

RestartApp() {
  Reload
}

Observer()

Server := new SocketTCP()
Server.OnAccept := Func("OnAccept")
Server.Bind([hostname, Port])
Server.Listen()
OnExit(Func("beforeExit"))

Observer() {
  global

  If !InStr(FileExist(Directory), "D") {
    MsgBox, 0, Error, "%Directory% " n'est pas valide!
    StopApp(1)
    Return
  }

  If !WatchFolder(Directory, "ActionHandler", True, 0x00000013) {
    MsgBox, 20, Erreur, Impossible d'observer le repertoir : `r`n%Directory% !`r`n`r`nVoulez-vous choisir une autre repertoir ?
    IfMsgBox, Yes
    {
      changeFold()
    }
    else
    {
      StopApp(1)
      Return
    }
  } else {
    TrayTip, Express Observer, L'observateur surveil deja au`r`nPORT : %Port%`r`nHaute : %hostname%`r`nRep : %Directory%, 7, 1
  }
}

ActionHandler(Folder, Change) {
  global 

  Data := [Folder, Change[1].Action, Change[1].Name, Change[1].IsDir, Change[1].OldName]
  Activity := 1
}

/*
; --> /ican
; <-- Action, Name, OldName, IsDir
*/
OnAccept(Server) {
  global

	Sock := Server.Accept()
	Request := StrSplit(Sock.RecvLine(), " ")
  while Line := Sock.RecvLine()
		Table .= Format("<tr><td>{}</td><td>{}</td></tr>", StrSplit(Line, ": ")*)
	if (Request[1] = "GET") {
    if (Request[2] == "/")
        Sock.SendText(Format(Template, "NO"))
    else if (Request[2] == "/ican") {
      sample = 
( Join`r`n
HTTP/1.0 200 OK
Content-Type: text/plain
Access-Control-Allow-Origin: *

{}
)
      if (Activity = 1) {
        data_ := Data[1] ";" Data[2] ";" Data[3] ";" Data[4] ";" Data[5]
        Activity := 0
        Sock.SendText(Format(sample, data_))
      } 
      else
      {
        Sock.SendText(Format(sample, "NO"))
      }
    } else
      Sock.SendText("HTTP/1.0 404 Not Found`r`n`r`n")
  } else {
		Sock.SendText("HTTP/1.0 501 Not Implemented`r`n`r`n")
		Sock.Disconnect()
		return
  }
	Sock.Disconnect()
}

beforeExit() {
  global
  WatchFolder("**END", "**DEL")
  Server.Disconnect()
  return 0
}
