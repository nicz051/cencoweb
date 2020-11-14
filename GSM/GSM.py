import serial
import serial.tools.list_ports
import requests
import time
import mysql.connector
import re
import datetime


def checkUART():
    ports = serial.tools.list_ports.comports()      #get list of ports
    for port, desc, hwid in sorted(ports):
            print("{}: {} [{}]".format(port, desc, hwid))
            ser = serial.Serial(port,9600,timeout=2)      # setup
            ser.write("AT\r\n".encode())

            while True:
                response = ser.readline()

                if ("OK"  or "ERROR") in str(response):
                    return ser

            ser.close()


def waitMessage(ser,message):
    while True:
        res = ser.readline()

        if (message or "ERROR") in str(res):
            return res


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
            try:
                sTxt = txt.index("$") #AT+CMGD=1,4
                eTxt = txt.index("*")+1
                txt = txt[sTxt:eTxt]
                messages.append({"index" : m_index,"number" : m_num, "message" : txt})
            except:
                pass
    return messages


def deleteMessage(ser,index):
    ser.write(f"AT+CMGD={index}\r\n".encode())
    waitMessage(ser,"OK")


def sendMessage(ser,number,activationCode):
    ser.write("AT+CMGF=1\r\n".encode())
    # time.sleep(0.1)
    waitMessage(ser,"OK")
    ser.write(f"AT+CMGS=\"{number}\"\r\n".encode())
    waitMessage(ser,">")
    ser.write(f"{activationCode}\x1A".encode())
    waitMessage(ser,"CMGS: ")


if __name__ == "__main__":

    mydb = mysql.connector.connect(
    host="localhost",
    user="root",
    password="root",
    database="ceneco_old"
    )

    mycursor = mydb.cursor()

    sql = "select id,mobilenum from posts"
    mycursor.execute(sql)
    posts = mycursor.fetchall()

    postsStatus = []
    ser = checkUART()

    for post in posts:
        post = list(post)[0]
        sql = f"select a.meter_number,a.status,p.mobilenum from `accounts` as a inner join `posts` as `p` on `accounts_posts_id` = `p`.`id` where exists (select 1 from `bills` where bills.bills_account_number = a.account_number and bills.disconnection_date <= CURDATE() and bills.status = 0) and a.accounts_posts_id  = {post} and a.updated_at IS NULL"
        mycursor.execute(sql)
        _post = mycursor.fetchall()

        if _post:
            postsStatus.append(_post)

    messages = []

    
    
    for postStatus in postsStatus:
        sms_body = ""
        meter = "$"
        status = "%"
        print("message")
        for r_index,resident in enumerate(postStatus):
            print("message")
            meter += str(resident[0]) + ","
            status += str("0") + ","
            # status += str(resident[1]) + ","
        sms_body += meter + status +"*"
        messages.append({"message":sms_body, "number" : resident[2]})
        print(sms_body+resident[2],ser)

        sendMessage(ser,resident[2],sms_body)
        # sendMessage(ser,"09615313752","$4,%0,*")
        # sendMessage(ser,"09987653629",sms_body)

        print("messagesent")
        

    time.sleep(10)                          # waits for replies
    tries = 3

    while 1:
        inbox = checkMessages(ser)
        indexesPop = []

        for m_index,message in enumerate(messages):
            for i_index,i_msg in enumerate(inbox):

                if (i_msg['message'] ==  message["message"] and i_msg["number"][3:] in message["number"]):

                    deleteMessage(ser,i_msg["index"])
                    indexesPop.append({"GSMinbox" : i_index , "sentMsg" : m_index})         # pop after iteration

                    meter_nums = i_msg["message"][1:].split("%")[0][:-1].split(",")

                    for meter_num in meter_nums:

                        sql = f"UPDATE `accounts` SET updated_at = '{datetime.datetime.now()}' WHERE meter_number = {meter_num}"
                        mycursor.execute(sql)
                        mydb.commit()
                else:
                    sendMessage(ser,message["number"],message["message"])
                    tries-=1
                    time.sleep(10)
                    if tries == 0:
                        messages.clear()
                        print(f"Check {message['number']} device where {message['message']}")
        for iP in indexesPop:
            inbox.pop(iP["GSMinbox"])
            messages.pop(iP["sentMsg"])
        if(len(messages) == 0):
            break