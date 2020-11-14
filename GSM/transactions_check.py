import serial
import serial.tools.list_ports
import requests
import mysql.connector
import datetime
import re
import time
import tkinter as tk
from tkinter import messagebox

def initSer():
    ports = serial.tools.list_ports.comports()      #get list of ports
    for port, desc, hwid in sorted(ports):
            print("{}: {} [{}]".format(port, desc, hwid))
            ser = serial.Serial(port,9600,timeout=2)      # setup
            ser.write("AT\r\n".encode())

            while True:
                response = ser.readline()
                if "OK"  or "ERROR" in str(response):
                    return ser
                    
def waitMessage(ser,message):
    while True:
        res = ser.readline()

        if (message or "ERROR") in str(res):
            return res

def initDB():
    mydb = mysql.connector.connect(
        host="localhost",
        user="root",
        password="root",
        database="ceneco_old"
    )
    return mydb

def parseTextMessage(transactions,ser):
    messages = []
    for t in transactions:
        messages.append({"number":t["number"],"message":f"${t['meter_num']},%{t['bills_status']},*"})
        sendMessage(ser,t["number"],f"${t['meter_num']},%{t['bills_status']},*")
    return messages

def sendMessage(ser,number,activationCode):
    ser.write("AT+CMGF=1\r\n".encode())
    waitMessage(ser,"OK")
    ser.write(f"AT+CMGS=\"{number}\"\r\n".encode())
    waitMessage(ser,">")
    ser.write(f"{activationCode}\x1A".encode())
    waitMessage(ser,"CMGS: ")

def fetchDB(db,sql):
    cursor = db.cursor()
    cursor.execute(sql)
    return cursor.fetchall()

def checkMessages(ser):                     # check and parse messages
    ser.write("AT+CMGL=\"ALL\"\r\n".encode())
    waitMessage(ser,"AT+CMGL=\"ALL\"")
    readMessage = ser.readlines()

    messages = []
    for index,message in enumerate(readMessage):
        if "+CMGL: " in str(message):
            _m = str(message).split(",")
            m_index     = int(re.search(r'\d+', _m[0]).group())
            m_num    = _m[2][1:-1]
            txt = str(readMessage[index+1])
            sTxt = txt.index("$")
            eTxt = txt.index("*")+1
            txt = txt[sTxt:eTxt]
            messages.append({"index" : m_index,"number" : m_num, "message" : txt})
    return messages

def deleteMessage(ser,index):
    ser.write(f"AT+CMGD={index}\r\n".encode())
    waitMessage(ser,"OK")

def numInText(string):
    return int(re.search(r'\d+', string).group())

if __name__ == "__main__":
    ser = None
    root = tk.Tk()
    root.withdraw()

    while ser == None:
        ser = initSer()
        if ser == None:
            time.sleep(2)
            messagebox.showerror("GSM module not connected", "Please insert GSM module. Press ok if connected properly.")
            
    mydb        = initDB()
    mycursor    = mydb.cursor()

    sql = ('SELECT '
        'b.status,(a.status)acc_status,a.meter_number,(p.mobilenum)num '
        'FROM bills AS b INNER JOIN transactions AS t ON t.transactions_bill_id = b.bill_id '
        'INNER JOIN accounts AS a ON a.account_number = b.bills_account_number '
        'INNER JOIN posts AS p ON a.accounts_posts_id = p.id  WHERE a.status = 0 GROUP BY a.meter_number')
    _transactions = fetchDB(mydb,sql)

    transcations = []
    for t in _transactions:
        transcations.append(
            {
                "bills_status"  : t[0],
                "acc_status"    : t[1],
                "meter_num"     : t[2],
                "number"        : t[3]
        })

    messages = parseTextMessage(transcations,ser)
    time.sleep(10)

    for message in messages:
        inbox = checkMessages(ser)
        for m in inbox:
            if(message["message"] == m["message"] and m["number"][3:] in message["number"]):
                # int(re.search(r'\d+', _m[0]).group())
                meter_num   = numInText(message["message"].split('%')[0])
                status      = numInText(message["message"].split('%')[1])

                sql = f"UPDATE `accounts` SET updated_at = '{datetime.datetime.now()}', status = {status} WHERE meter_number = {meter_num}"
                deleteMessage(ser,m["index"])
    