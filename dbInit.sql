
#
#personinfo表
#username 用户名, realname 真名, grade 年级, academy 学院, StuNum 学号, mail 邮箱, mobilephone 手机， im QQ号



DROP TABLE IF EXISTS  `personinfo`;

CREATE TABLE `personinfo` (
  `username` char(20) NOT NULL default '',
  `realname` char(20) default NULL,
  `grade` char(20) default NULL,
  `academy` int(11) default NULL,
  `StuNum` char(20) default NULL,
  `mail` char(30) default NULL,
  `mobilephone` char(20) default NULL,
  `im` char(30) default NULL,
  PRIMARY KEY  (`username`)
);


#academynum 学院与其对应的名称和编号，
CREATE TABLE `academynum` (
  `num` int(11) default NULL,
  `value` char(40) default NULL
) ;
INSERT INTO `academynum` VALUES (1,'通信工程学院'),(2,'电子工程学院'),(3,'计算机学院'),(4,'机电工程学院'),(5,'技术物理学院'),(6,'经济管理学院'),(7,'人文学院'),(8,'理学院'),(9,'微电子学院'),(10,'软件学院'),(11,'长安学院'),(12,'网络与继续教育学院'),(13,'生命科学技术学院'),(14,'国际教育学院'),(0,'');




#创建training表,记录用户的训练情况,字段有:
#username用户名, ojType 哪个OJ, time 查询时间, queryID 查询用的ID, value 查询得到的值
#不能用后面的约束###
create table training(username char(20) not null, ojType char(20), time datetime, queryID char(20), value int , foreign key(username) references personinfo.username);



#创建oj列表,存储:oj名称, 对外显示的名称, 是否需要用户填写ojID, 是否自动抓取(如果不是代表此OJ需要手动填写),手动填写时格式有效性的正则表达式,自动查询时的返回值正则验证表达式, 悬停的title里的说明信息, 最大允许同时发起的抓取连接数,最短的抓取时间间隔(单位是秒), 单次抓取的超时时间,单位是秒, 备注,是否需要OJ的密码.needOJPass,是否需要填写密码
###表建立之后要手动插入pku,zju等传统oj的数据,还要加入syn这个特殊的OJ,它用来更新产生最新一组加权值.
create table ojList(
	ojName char(20),
	ojDisplay char(40), 
	needOJID bool, 
	manuallyInput bool, 
	checkReg char(80), 
	retReg char(80),
	title char(100), 
	maxQuerySize int,
	minQueryTimeInterval float,
	overTime float,
	maxFailTimes int,
	ojRemark varchar(200),
	needOJPass bool,
	ojWeight float
);

insert into ojList(ojName,ojDisplay,needOJID,manuallyInput,checkReg,retReg,title,maxQuerySize,minQueryTimeInterval,overTime,maxFailTimes,ojRemark,needOJPass,ojWeight) values('pku','pku',True,False,'','[0-9]+','北京大学 pku Online Judge',10,0,20,2,"存储的数据是在pku做的题目数量",False,1);
insert into ojList(ojName,ojDisplay,needOJID,manuallyInput,checkReg,retReg,title,maxQuerySize,minQueryTimeInterval,overTime,maxFailTimes,ojRemark,needOJPass,ojWeight) values('zju','zju',True,False,'','[0-9]+','浙江大学 zju Online Judge',10,0,20,2,"存储的数据是在zju做的题目数量",False,1);
insert into ojList(ojName,ojDisplay,needOJID,manuallyInput,checkReg,retReg,title,maxQuerySize,minQueryTimeInterval,overTime,maxFailTimes,ojRemark,needOJPass,ojWeight) values('hdu','hdu',True,False,'','[0-9]+','杭州电子科技大学 hdu Online Judge',10,0,20,2,"存储的数据是在hdu做的题目数量",False,1);
insert into ojList(ojName,ojDisplay,needOJID,manuallyInput,checkReg,retReg,title,maxQuerySize,minQueryTimeInterval,overTime,maxFailTimes,ojRemark,needOJPass,ojWeight) values('tc','TopCoder',True,False,'','[0-9]+','TopCoder',10,0,20,2,"存储的数据是tc的rating*10000+场数",False,1/20000);
insert into ojList(ojName,ojDisplay,needOJID,manuallyInput,checkReg,retReg,title,maxQuerySize,minQueryTimeInterval,overTime,maxFailTimes,ojRemark,needOJPass,ojWeight) values('cf','CodeForces',True,False,'','[0-9]+','CodeForces',10,0,60,2,"存储的数据是cf的rating*10000+场数",False,1/20000);
insert into ojList(ojName,ojDisplay,needOJID,manuallyInput,checkReg,retReg,title,maxQuerySize,minQueryTimeInterval,overTime,maxFailTimes,ojRemark,needOJPass,ojWeight) values('usaco','Usaco',True,False,'[1-6]\\.[1-7]','','Usaco Training Gate Way',10,20,20,2,"存储的数据是usaco的章节乘以10",True,500);

#创建oj的用户ID绑定列表, 原则上是不允许用户在某个OJ拥有两个或多个ID的,但是后台数据库设计的时候要允许可能的扩展,所以在这里不限制用户ID的存取, 唯一性由应用程序来保证. 内容为四部分: 用户ID,oj类型, 用户在oj的ID,用户在oj的密码
#约束没有满足###！
create table userIDOnOJ(	
	username char(20), 
	ojType char(20), 
	ojID char(40),
	ojPass char(80),
	foreign key (username) references personinfo(username),
	foreign key (ojType) references ojList(ojName)
);


#视图RecentQueryTime,不同用户不同oj不同ID最近的查询时间
create view RecentQueryTime as(
	select username,ojType,queryID,max(time) as time from training group by username,ojType,queryID
);
#建立视图 RecentTrainingQuery 它的作用是选出 training 表里按用户填写的ID相符的最新的一组训练信息,即
#  ID + ojType + ojID + 时间最新 -> 选出.
#  注释掉的是一个清晰可视版, 没有注册掉的用来执行.两者相同
#
#create view RecentTrainingQuery as(
#	select tr.username as username,tr.ojType as ojType,tr.queryID as queryID,tr.time as updateTime,tr.value as value 	
#	fromt training tr,RecentQueryTime as t
#	where tr.username=t.username and tr.ojType=t.ojType and tr.queryID=t.queryID and tr.time=t.time
#);

create view RecentTrainingQuery as( select tr.username as username,tr.ojType as ojType,tr.queryID as queryID,tr.time as updateTime,tr.value as value 	from training tr,RecentQueryTime as t where tr.username=t.username and tr.ojType=t.ojType and tr.queryID=t.queryID and tr.time=t.time);


#每添加一条用户ID进入系统的时候要先检测,
#1. 某ID是否是manuallyInput的, 要不要显示表单进行数据添加,这里是personInfo进行的工作.
#2. 添加以后要进行验证,是否有用户要对不能manually添加的表单进行了添加, 有则拒绝添加.这个是addUserIDOnOJ.php做的工作,方法为get.



#将 RecentTrainingQuery 所有内容选择出来,按用户名分组并对每个用户给出一个syn值.
#按此syn值对用户进行排序,输出用户training表,这些工作在更新用户的信息列表的时候产生.


#updateTaskList
#这个表用来和后台的python服务交互, python进程靠这个表识别是否有新的刷新任务.
#ojType 是oj类型, id是用户在oj上的id, querytime是任务的提交时间,doneTime是任务结束的时间. status 是任务状态, failTime是目前已经失败查询的次数。任务状态有以下几种取值：
#
# 0 已经提交, 还未加入队列
# 1 已经加入等待队列,未开辟进程
# 2 已经开辟进程
# 3 超时重查
# 4 超时退出
# 5 返回格式错,重试
# 6 返回格式错,退出
# 7 查询成功结束
#
create table updateTaskList(
	username char(20),
	ojType char(20),
	id char(40),
	queryTime datetime,	
	status int default 0,
	failTimes int default 0,
	doneTime datetime
);


