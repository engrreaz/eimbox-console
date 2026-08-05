/* ============================================================
   EIMBox Analytics Engine
   Version : 1.0
   File    : install.sql
   Part    : 1 (Foundation)
   ============================================================ */

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE DATABASE IF NOT EXISTS eimbox_analytics
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE eimbox_analytics;





/* ============================================================
   Analytics Dataset Registry
   ============================================================ */

CREATE TABLE analytics_dataset(

    datasetid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    sccode INT NOT NULL,

    sessionyear SMALLINT NOT NULL,

    examid INT NOT NULL,

    classid INT NOT NULL,

    groupid INT DEFAULT 0,

    sectionid INT DEFAULT 0,

    shiftid INT DEFAULT 0,

    versionid INT DEFAULT 0,

    dataset_name VARCHAR(200),

    total_students INT DEFAULT 0,

    total_subjects INT DEFAULT 0,

    createdby INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NULL DEFAULT NULL,

    status TINYINT DEFAULT 1,

    UNIQUE KEY uq_dataset(
        sccode,
        sessionyear,
        examid,
        classid,
        groupid,
        sectionid,
        shiftid,
        versionid
    ),

    INDEX idx_school(sccode),
    INDEX idx_exam(examid),
    INDEX idx_class(classid)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;





/* ============================================================
   Analytics Jobs Queue
   ============================================================ */

CREATE TABLE analytics_jobs(

    jobid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    datasetid BIGINT UNSIGNED NOT NULL,

    jobtype VARCHAR(100),

    progress DECIMAL(5,2) DEFAULT 0,

    totalstep INT DEFAULT 0,

    completedstep INT DEFAULT 0,

    started_at DATETIME NULL,

    finished_at DATETIME NULL,

    runtime_second INT DEFAULT 0,

    status ENUM(
        'Pending',
        'Running',
        'Completed',
        'Failed'
    ) DEFAULT 'Pending',

    errmsg TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(datasetid),

    CONSTRAINT fk_job_dataset
        FOREIGN KEY(datasetid)
        REFERENCES analytics_dataset(datasetid)
        ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;





/* ============================================================
   Snapshot Information
   ============================================================ */

CREATE TABLE analytics_snapshot(

    snapshotid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    datasetid BIGINT UNSIGNED NOT NULL,

    snapshot_name VARCHAR(200),

    total_students INT,

    total_pass INT,

    total_fail INT,

    pass_percentage DECIMAL(8,2),

    average_gpa DECIMAL(8,3),

    highest_gpa DECIMAL(5,2),

    lowest_gpa DECIMAL(5,2),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(datasetid),

    CONSTRAINT fk_snapshot_dataset
        FOREIGN KEY(datasetid)
        REFERENCES analytics_dataset(datasetid)
        ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;






/* ============================================================
   Analytics Cache
   ============================================================ */

CREATE TABLE analytics_cache(

    cacheid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    datasetid BIGINT UNSIGNED,

    cachekey VARCHAR(250),

    cachevalue LONGTEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    expire_at DATETIME,

    UNIQUE KEY uq_cache(
        datasetid,
        cachekey
    ),

    INDEX(expire_at),

    CONSTRAINT fk_cache_dataset
        FOREIGN KEY(datasetid)
        REFERENCES analytics_dataset(datasetid)
        ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;






/* ============================================================
   Analytics Log
   ============================================================ */

CREATE TABLE analytics_log(

    logid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    datasetid BIGINT UNSIGNED,

    userid INT,

    activity VARCHAR(200),

    details TEXT,

    ipaddress VARCHAR(50),

    logtime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(datasetid),
    INDEX(userid),
    INDEX(logtime)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;






/* ============================================================
   System Configuration
   ============================================================ */

CREATE TABLE analytics_settings(

    settingid INT AUTO_INCREMENT PRIMARY KEY,

    settingkey VARCHAR(150),

    settingvalue LONGTEXT,

    UNIQUE KEY(settingkey)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;






INSERT INTO analytics_settings(settingkey,settingvalue)
VALUES
('ENGINE_VERSION','1.0'),
('CACHE_ENABLE','YES'),
('CACHE_EXPIRE_MINUTE','30'),
('AUTO_REBUILD','YES'),
('MAX_JOB_PER_QUEUE','5');



/* ============================================================
   Teacher Performance Snapshot
   ============================================================ */

CREATE TABLE analytics_teacher_performance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    datasetid BIGINT UNSIGNED NOT NULL,
    sccode INT NOT NULL,
    sessionyear SMALLINT NOT NULL,
    examid INT NOT NULL,
    classname VARCHAR(50) NOT NULL,
    sectionname VARCHAR(50) NOT NULL,
    subjectid INT NOT NULL,
    teacherid INT NOT NULL,
    total_students INT DEFAULT 0,
    total_pass INT DEFAULT 0,
    total_fail INT DEFAULT 0,
    pass_rate DECIMAL(5, 2) DEFAULT 0.00,
    average_gpa DECIMAL(4, 2) DEFAULT 0.00,
    average_marks DECIMAL(5, 2) DEFAULT 0.00,
    highest_gpa DECIMAL(4, 2) DEFAULT 0.00,
    lowest_gpa DECIMAL(4, 2) DEFAULT 0.00,
    a_plus_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_teacher_performance (
        datasetid,
        teacherid,
        classname,
        sectionname,
        subjectid
    ),

    CONSTRAINT fk_tp_dataset
        FOREIGN KEY (datasetid)
        REFERENCES analytics_dataset(datasetid)
        ON DELETE CASCADE,

    INDEX idx_teacher (teacherid),
    INDEX idx_subject (subjectid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



SET FOREIGN_KEY_CHECKS=1;