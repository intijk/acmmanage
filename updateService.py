#!/usr/bin/python2.6
#coding=utf-8
import sys
import os
import re
import subprocess
import MySQLdb
import conf
import time
#打开日志文件
logFile='';
if len(sys.argv)==2:
	logFile=open(sys.argv[1],'a')
else:
	logFile=open("logUpdateService",'a')
logTmp=open("/tmp/updateServiceLog",'w+')

sys.stdout=logTmp
sys.stderr=logFile
#数据库打开的初始化
conn=MySQLdb.connect(host=conf.dbHost,user=conf.dbUser,passwd=conf.dbPasswd,db=conf.dbName)
cursor=conn.cursor(MySQLdb.cursors.DictCursor)
#引入oj列表的初始化
cursor.execute("select * from ojList where needOJID=True")
ojList=cursor.fetchall()
queryList={'empty':'empty'}
del(queryList['empty'])
for r in ojList:
	queryList[r['ojName']]={'size':0}
	queryList[r['ojName']]['maxSize']=r['maxQuerySize']
	queryList[r['ojName']]['minTimeInterval']=r['minQueryTimeInterval']
	queryList[r['ojName']]['maxTimeInterval']=r['overTime']
	queryList[r['ojName']]['maxFailTimes']=r['maxFailTimes']
	queryList[r['ojName']]['retReg']=r['retReg']
	queryList[r['ojName']]['lastQueryTime']=0
	queryList[r['ojName']]['tasks']=[0]
	queryList[r['ojName']]['tasks'].pop(0)
roundTime=0
while True:
	logPrint=0	
	print '======================================'
	print '刷新轮数',roundTime,'('+time.strftime('%Y-%m-%d %H:%M:%S')+')'

	roundTime+=1
	cursor.execute("select * from updateTaskList where status=0 order by queryTime asc")
	res=cursor.fetchall();
	print '新加入的任务数=',len(res)
	if len(res)>0:
		logPrint=1
		for r in res:
			if queryList[r['ojType']]['size'] < queryList[r['ojType']]['maxSize']:
				r['status']=1
				queryList[r['ojType']]['tasks'].append(r)
				queryList[r['ojType']]['size']=queryList[r['ojType']]['size']+1
				cursor.execute("update updateTaskList set status=" + str(r['status']) + " where ojType='" + str(r['ojType']) + "' and id='" + str(r['id']) + "' and queryTime='" + str(r['queryTime']) + "' and username='" + str(r['username']) + "'")
			
	for x in queryList:
		oj=queryList[x]
		print 'ojType=',x,',size=',oj['size']
		for c in oj['tasks']:
			logPrint=1
			firstTask=oj['tasks'].pop(0)
			if firstTask['status']==2:
				#返回值不为None的时候就代表进程已经结束
				if firstTask['process'].poll()!=None:
					ret=str(firstTask['process'].stdout.read())
					#返回了结果,并且结果格式正确
					if re.search(oj['retReg'],ret):
						print "任务:username='"+str(firstTask['username'])+"' ojType='"+str(firstTask['ojType'])+"' id='"+str(firstTask['id'])+"' queryTime='"+str(firstTask['queryTime'])+"' 在"+time.strftime('%Y-%m-%d %H:%M:%S')
						print "查询成功"
						firstTask['doneTime']=time.strftime('%Y-%m-%d %H:%M:%S')
						firstTask['status']=7 #2 代表查询完毕
						sql="select * from RecentTrainingQuery where username='" + str(firstTask['username']) + "' and ojType='"+str(firstTask['ojType'])+ "' and queryID='" + str(firstTask['id']) +"'";	
						cursor.execute(sql)
						resAll=cursor.fetchall();
						if len(resAll)!=0:	
							res=resAll[0];
						if len(resAll)==0 or str(res['value'])!=ret:
							#如果得到了一个新值,则加入新training纪录
							sql="insert into training(username,ojType,time,queryID,value) values('" + str(firstTask['username']) + "','" + str(firstTask['ojType']) + "','" + str(firstTask['doneTime']) + "','" + str(firstTask['id']) + "','" + str(ret) +"')";
							cursor.execute(sql)

						#查询完成,修改任务状态,size=size-1
						cursor.execute("update updateTaskList set doneTime='" + str(firstTask['doneTime']) + "',status=" + str(firstTask['status']) + " where ojType='" + str(firstTask['ojType']) + "' and id='" + str(firstTask['id']) + "' and queryTime='" + str(firstTask['queryTime']) + "' and username='" + str(firstTask['username']) + "'")
						subprocess.Popen(["./updatesyn.php",firstTask['username']],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,shell=False)
						oj['size']=oj['size']-1;
					#这里的else表示返回了结果,但结果不正确,所以设置查询失败,重试查询
					else:
						#首先更新下syn	

						subprocess.Popen(["./updatesyn.php",firstTask['username']],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,shell=False)

						print "任务:username='"+str(firstTask['username'])+"' ojType='"+str(firstTask['ojType'])+"' id='"+str(firstTask['id'])+"' queryTime='"+str(firstTask['ojType'])+"' 在"+time.strftime('%Y-%m-%d %H:%M:%S')
						print "返回了错误格式的结果",ret
						#如果在容许失败次数内
						firstTask['failTimes']=firstTask['failTimes']+1
						print "当前失败次数",firstTask['failTimes']
						if firstTask['failTimes']<oj['maxFailTimes']:
							firstTask['status']=5 #5 代表格式错,重试
							print "重试抓取"
							cursor.execute("update updateTaskList set status=" + str(firstTask['status']) + ",failTimes="+str(firstTask['failTimes'])+" where ojType='" + str(firstTask['ojType']) + "' and id='" + str(firstTask['id']) + "' and queryTime='" + str(firstTask['queryTime']) + "' and username='" + str(firstTask['username']) + "'")
							oj['tasks'].append(firstTask);
						#如果失败次数太多,则置状态并删除任务
						else:
							firstTask['status']=6 #6 代表格式错,退出
							cursor.execute("update updateTaskList set status=" + str(firstTask['status']) + ",failTimes="+str(firstTask['failTimes'])+ " where ojType='" + str(firstTask['ojType']) + "' and id='" + str(firstTask['id']) + "' and queryTime='" + str(firstTask['queryTime']) + "' and username='" + str(firstTask['username']) + "'")
							oj['size']=oj['size']-1
							print "格式错退出"
				#如果超时,杀掉任务,置失败次数,失败次数小于最大失败次数则重入
				elif firstTask['lastQueryTime']+oj['maxTimeInterval']<=time.time():
						print "任务:username='"+str(firstTask['username'])+"' ojType='"+str(firstTask['ojType'])+"' id='"+str(firstTask['id'])+"' queryTime='"+str(firstTask['ojType'])+"' 在"+time.strftime('%Y-%m-%d %H:%M:%S')
						print "产生超时"
						firstTask['process'].terminate()
						firstTask['failTimes']=firstTask['failTimes']+1
						print "当前失败次数",firstTask['failTimes']
						#如果失败次数在容许失败次数内,置位重入
						if firstTask['failTimes']<oj['maxFailTimes']:
							firstTask['status']=3 #3 代表超时重试
							cursor.execute("update updateTaskList set status=" + str(firstTask['status']) + ",failTimes="+str(firstTask['failTimes'])+" where ojType='" + str(firstTask['ojType']) + "' and id='" + str(firstTask['id']) + "' and queryTime='" + str(firstTask['queryTime']) + "' and username='" + str(firstTask['username']) + "'")
							oj['tasks'].append(firstTask);
							print "重试抓取"
						#如果失败次数过多,置标志位退出
						else:
							firstTask['status']=4 #4 代表超时退出
							cursor.execute("update updateTaskList set status=" + str(firstTask['status']) + ",failTimes="+str(firstTask['failTimes'])+" where ojType='" + str(firstTask['ojType']) + "' and id='" + str(firstTask['id']) + "' and queryTime='" + str(firstTask['queryTime']) + "' and username='" + str(firstTask['username']) + "'")
							oj['size']=oj['size']-1
							print "失败退出"
				#未完成也未超时,直接重入
				else:
					oj['tasks'].append(firstTask)
			#状态为不为1,则要么是新加入的状态为0的新任务,要么是允许失败次数以内的任务
			else:
				#如果提供的用户名为空,则直接设置成功退出
				if firstTask['id']=='':
					firstTask['status']=7
					firstTask['doneTime']=time.strftime('%Y-%m-%d %H:%M:%S');
					cursor.execute("update updateTaskList set doneTime='" + str(firstTask['doneTime']) + "',status=" + str(firstTask['status']) + " where ojType='" + str(firstTask['ojType']) + "' and id='" + str(firstTask['id']) + "' and queryTime='" + str(firstTask['queryTime']) + "' and username='" + str(firstTask['username']) + "'")
					oj['size']=oj['size']-1
				#如果提供的用户名不为空,则开辟进程并且更新数据库中的状态.
				else:
					#if 当前时间大于此oj最近查询时间+最短时间间隔 , 即刷新过快 
					if time.time() > oj['lastQueryTime']+oj['minTimeInterval']:
						firstTask['process']=subprocess.Popen([conf.grabProgramPath,firstTask['username'],firstTask['ojType'],firstTask['id']],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,shell=False)
						firstTask['status']=2
						cursor.execute("update updateTaskList set status="+str(firstTask['status'])+" where ojType='" + str(firstTask['ojType']) + "' and id='" + str(firstTask['id']) + "' and queryTime='"+str(firstTask['queryTime'])+"' and username='" + str(firstTask['username']) + "'")
						print "任务:username='"+str(firstTask['username'])+"' ojType='"+str(firstTask['ojType'])+"' id='"+str(firstTask['id'])+"' queryTime='"+str(firstTask['queryTime'])+"' 在"+time.strftime('%Y-%m-%d %H:%M:%S')
						print '开辟进程'
						#此oj最近查询时间='当前时间';
						firstTask['lastQueryTime']=oj['lastQueryTime']=time.time();
					#不管是否开始了此任务,都需要重新加回队列,让后面去判断完成也好,重新开始也好.
					oj['tasks'].append(firstTask);
	sys.stdout.flush()
	#如果发生了某些事件才写日志
	if logPrint==1:
		logTmp.seek(0);
		logFile.write(logTmp.read())
		logFile.flush()
	logTmp.truncate(0);
	logTmp.seek(0);
	time.sleep(3);
