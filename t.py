#!/usr/bin/python2.7
#coding=utf-8
import MySQLdb
import conf
conn=MySQLdb.connect(host=conf.dbHost,user=conf.dbUser,passwd=conf.dbPasswd,db=conf.dbName)
c=conn.cursor();
c.execute("create table test(t int)");
c.execute("insert into test(t) values(100)");
