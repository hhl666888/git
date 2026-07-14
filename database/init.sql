-- ============================================================
-- AI 产品经理个人官网 — 数据库初始化脚本
-- 包含：建库、建表、初始数据导入
-- 使用方法：在 MySQL 命令行执行  source init.sql
-- 或在 phpMyAdmin 中直接导入此文件
-- ============================================================

-- 创建数据库（如不存在）
CREATE DATABASE IF NOT EXISTS root
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE root;
-- ============================================================
-- 1. 个人基本信息表（单行配置，全站共享）
-- ============================================================
DROP TABLE IF EXISTS site_profile;
CREATE TABLE site_profile (
  id          INT PRIMARY KEY DEFAULT 1,
  name        VARCHAR(100)  NOT NULL COMMENT '姓名',
  slogan      VARCHAR(255)  NOT NULL COMMENT '一句话标语',
  intro       TEXT          NOT NULL COMMENT '个人简介',
  github      VARCHAR(255)  DEFAULT '' COMMENT 'GitHub 主页地址',
  email       VARCHAR(100)  DEFAULT '' COMMENT '邮箱地址',
  resume      VARCHAR(255)  DEFAULT 'assets/files/resume.pdf' COMMENT '简历文件路径',
  badges      JSON          COMMENT '首页标签数组，如["AI大模型","Prompt工程"]',
  updated_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='个人基本信息（全站共享）';

-- ============================================================
-- 2. 首页数据统计卡片
-- ============================================================
DROP TABLE IF EXISTS stats;
CREATE TABLE stats (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  value       VARCHAR(50)   NOT NULL COMMENT '显示数值，如 5+',
  label       VARCHAR(200)  NOT NULL COMMENT '描述文字',
  sort_order  INT           DEFAULT 0 COMMENT '排序（越小越靠前）'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='首页数据卡片';

-- ============================================================
-- 3. 核心能力卡片
-- ============================================================
DROP TABLE IF EXISTS capabilities;
CREATE TABLE capabilities (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(100)  NOT NULL COMMENT '能力标题',
  description TEXT          NOT NULL COMMENT '能力描述',
  sort_order  INT           DEFAULT 0 COMMENT '排序'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='核心能力卡片';

-- ============================================================
-- 4. 项目作品集
-- ============================================================
DROP TABLE IF EXISTS projects;
CREATE TABLE projects (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  slug              VARCHAR(100)  NOT NULL UNIQUE COMMENT 'URL标识（英文），用于详情页地址',
  name              VARCHAR(200)  NOT NULL COMMENT '项目名称',
  type              VARCHAR(100)  NOT NULL COMMENT '项目类型',
  intro             TEXT          NOT NULL COMMENT '项目简介',
  role              VARCHAR(255)  NOT NULL COMMENT '我的职责',
  result            VARCHAR(255)  NOT NULL COMMENT '成果数据',
  cover             VARCHAR(100)  DEFAULT '' COMMENT '封面文字（无图片时显示）',
  github            VARCHAR(255)  DEFAULT '' COMMENT 'GitHub 仓库地址',
  tags              JSON          COMMENT '项目标签数组',
  -- 详情页扩展字段
  detail_background TEXT          COMMENT '项目背景',
  detail_prd        JSON          COMMENT 'PRD 要点数组',
  detail_screenshots JSON         COMMENT '截图说明数组',
  detail_metrics    JSON          COMMENT '指标数据数组',
  detail_review     TEXT          COMMENT '复盘总结',
  sort_order        INT           DEFAULT 0 COMMENT '排序',
  created_at        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目作品集';

-- ============================================================
-- 5. 笔记分类
-- ============================================================
DROP TABLE IF EXISTS note_categories;
CREATE TABLE note_categories (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(50)   NOT NULL UNIQUE COMMENT '分类名称',
  sort_order  INT           DEFAULT 0 COMMENT '排序'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='笔记分类';

-- ============================================================
-- 6. 学习笔记
-- ============================================================
DROP TABLE IF EXISTS notes;
CREATE TABLE notes (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  slug         VARCHAR(100)  NOT NULL UNIQUE COMMENT 'URL标识（英文）',
  title        VARCHAR(200)  NOT NULL COMMENT '笔记标题',
  date         DATE          NOT NULL COMMENT '发布日期',
  category_id  INT           NOT NULL COMMENT '分类ID（关联 note_categories.id）',
  summary      TEXT          NOT NULL COMMENT '摘要',
  content      MEDIUMTEXT    NOT NULL COMMENT '正文（支持 Markdown 语法）',
  tags         JSON          COMMENT '标签数组',
  sort_order   INT           DEFAULT 0 COMMENT '排序',
  created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  -- 外键约束：分类被删除时笔记不丢失，仅置空分类
  CONSTRAINT fk_notes_category FOREIGN KEY (category_id)
    REFERENCES note_categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习笔记';

-- ============================================================
-- 7. 关于我 — 基础配置（单行）
-- ============================================================
DROP TABLE IF EXISTS about_info;
CREATE TABLE about_info (
  id           INT PRIMARY KEY DEFAULT 1,
  plan         TEXT NOT NULL COMMENT '职业规划',
  evaluation   TEXT NOT NULL COMMENT '自我评价',
  tools        JSON           COMMENT '工具标签数组',
  updated_at   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='关于我基础配置';

-- ============================================================
-- 8. 关于我 — 教育经历（多条）
-- ============================================================
DROP TABLE IF EXISTS about_education;
CREATE TABLE about_education (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  content     VARCHAR(500)  NOT NULL COMMENT '教育经历描述',
  sort_order  INT           DEFAULT 0 COMMENT '排序'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='教育经历';

-- ============================================================
-- 9. 关于我 — 工作经历（多条）
-- ============================================================
DROP TABLE IF EXISTS about_work;
CREATE TABLE about_work (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  content     VARCHAR(500)  NOT NULL COMMENT '工作经历描述',
  sort_order  INT           DEFAULT 0 COMMENT '排序'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='工作经历';


-- ============================================================
-- ====================  初始数据导入  ====================
-- ============================================================

-- 个人基本信息
INSERT INTO site_profile (id, name, slogan, intro, github, email, resume, badges) VALUES
(1, '黄虹龄', '把 AI 能力转化成可落地、可增长、可复用的产品价值',
 '专注 AI 大模型应用落地、Prompt 工程、AI 工具产品设计与多模态需求拆解，擅长从 0 到 1 推动项目上线，并用数据复盘产品价值。',
 'https://github.com/your-name', 'hello@example.com', 'assets/files/resume.pdf',
 '["AI 大模型应用落地", "Prompt 工程", "多模态产品", "PRD 撰写", "从 0 到 1", "商业化思考"]');

-- 首页数据卡片
INSERT INTO stats (value, label, sort_order) VALUES
('5+',   'AI 项目从 0 到 1',                       1),
('30+',  '笔记与行业复盘',                         2),
('100%', '静态部署，GitHub Pages 即开即用',         3),
('2 模式','编辑模式 / 展示模式一键切换',            4);

-- 核心能力卡片
INSERT INTO capabilities (title, description, sort_order) VALUES
('大模型需求设计', '把业务目标拆成可执行的模型能力、上下文和交互方案。', 1),
('Prompt 体系搭建', '设计模板、评估标准、提示词库和复用机制。', 2),
('多模态产品', '围绕图文、语音、视频与文档输入输出设计用户流程。', 3),
('数据分析', '建立埋点、看板、转化漏斗和效果评估闭环。', 4),
('跨团队协作', '推动算法、工程、设计、运营与业务的协同交付。', 5),
('PRD 输出', '把复杂 AI 需求写成清晰、可落地、可评审的文档。', 6);

-- 项目作品集
INSERT INTO projects (slug, name, type, intro, role, result, cover, github, tags,
  detail_background, detail_prd, detail_screenshots, detail_metrics, detail_review, sort_order) VALUES
('ai-assistant', '企业知识库 AI 助手', 'AI 应用 / Copilot',
 '为内部员工提供知识问答、文档总结和流程指引，提升信息检索效率。',
 '需求分析、问答链路设计、产品验证、指标定义',
 '检索效率提升 60%，FAQ 重复咨询下降 35%。',
 'Knowledge Copilot', 'https://github.com/your-name/ai-assistant',
 '["RAG", "知识库", "效率工具"]',
 '公司内部文档分散、员工找信息成本高，希望通过 AI 问答统一入口。',
 '["支持文档检索 + 答案引用来源", "支持高频问题推荐与反馈纠错", "对接权限体系和文档同步机制"]',
 '["原型截图位 1", "原型截图位 2"]',
 '["平均响应时间 3.2s", "知识命中率 82%", "满意度 4.7/5"]',
 '关键在于把问答体验做成可信、可追溯、可运营，而不是只堆模型能力。',
 1),
('prompt-platform', 'Prompt 运营平台', '工具平台 / 内部效率',
 '支持提示词管理、版本对比、效果评估和团队复用，统一 AI 输出质量。',
 'Prompt 资产设计、评测流程、协作机制',
 '复用率提升 50%，输出稳定性提升明显。',
 'Prompt Ops', 'https://github.com/your-name/prompt-platform',
 '["Prompt", "评测", "协作"]',
 '团队各自维护提示词，缺少版本管理和评估标准。',
 '["Prompt 模板库与标签体系", "版本记录、灰度发布和回滚", "效果打分与 A/B 对比面板"]',
 '["版本管理截图", "评测结果截图"]',
 '["复用率 +50%", "错误率 -22%", "评估覆盖率 100%"]',
 'Prompt 管理本质上是知识资产管理，产品化后才能真正规模化。',
 2),
('multimodal-review', '多模态内容审核系统', 'AI 业务 / 风控辅助',
 '结合图文识别和规则引擎，提升审核效率并降低人工漏检。',
 '需求拆解、审核路径设计、异常处理方案',
 '人工审核时长缩短 40%，漏检率明显下降。',
 'Multimodal Review', 'https://github.com/your-name/multimodal-review',
 '["多模态", "风控", "审核"]',
 '内容审核场景中，单靠人工效率低、成本高。',
 '["图片、文本、元信息联合判定", "低置信度转人工复核", "审核记录沉淀为训练与复盘数据"]',
 '["审核流程图", "异常样本列表"]',
 '["时长 -40%", "漏检率 -18%", "复核一致率 +12%"]',
 '多模态产品的重点不是模型花哨，而是把不确定性处理好。',
 3);

-- 笔记分类
INSERT INTO note_categories (name, sort_order) VALUES
('大模型学习',   1),
('Prompt工程',   2),
('工作复盘',     3),
('行业资讯',     4),
('书籍笔记',     5),
('产品方法论',   6);

-- 学习笔记
INSERT INTO notes (slug, title, date, category_id, summary, content, tags, sort_order) VALUES
('llm-product-thinking', '大模型产品设计：从能力到场景的拆解方法', '2026-07-03', 1,
 '记录把模型能力拆成可交付产品方案的思路：场景、链路、指标与风险。',
 '# 大模型产品设计：从能力到场景的拆解方法

> 目标：把模型能力翻译成产品能力，而不是把模型能力直接当产品。

## 一、先定义场景

- 用户是谁
- 频率有多高
- 任务是否高价值
- 结果是否可以量化

## 二、再拆链路

1. 输入是否明确
2. 模型是否能稳定理解
3. 输出是否可验证
4. 是否需要人工兜底

## 三、最后看指标

| 指标 | 解释 |
| --- | --- |
| 命中率 | 用户问题被正确回答的比例 |
| 满意度 | 用户对结果的主观评价 |
| 转化率 | 是否推动了下一步行为 |

## 四、结论

AI 产品经理最重要的能力，是在能力、约束和业务目标之间找到平衡。',
 '["大模型", "产品方法论"]', 1),

('prompt-iteration', 'Prompt 迭代的 5 个实战原则', '2026-06-26', 2,
 '总结如何设计、测试、评估和沉淀提示词资产。',
 '# Prompt 迭代的 5 个实战原则

## 1. 先写输入标准

先规定什么信息必须提供，再写提示词。

## 2. 让输出结构稳定

用固定标题、表格和步骤降低阅读成本。

## 3. 给模型边界

明确不能做什么、哪些信息要保留不确定性。

## 4. 做版本管理

每次调整都记录版本号和修改原因。

## 5. 结合真实样本评估

不要只看感觉，要看真实任务的成功率。',
 '["Prompt", "效率"]', 2),

('product-review', 'AI 产品复盘：为什么功能上线后还需要继续运营', '2026-06-18', 3,
 '从数据、反馈和业务目标三个层面复盘 AI 产品上线后的运营动作。',
 '# AI 产品复盘：为什么功能上线后还需要继续运营

产品上线只是开始，不是结束。

## 关键三件事

- 看数据，确认是否真的被使用
- 看反馈，确认用户卡在哪里
- 看目标，确认是否带来业务收益

## 常见问题

- 首次使用门槛过高
- 引导不清晰
- 缺少持续运营策略

## 复盘结论

AI 产品需要"产品设计 + 运营机制 + 数据复盘"三位一体，才能真正长期增长。',
 '["复盘", "增长"]', 3),

('industry-insight-2026', '2026 AI 产品行业观察：Agent、RAG 与工作流融合趋势', '2026-06-10', 4,
 '整理近期 AI 产品的核心趋势和可落地机会。',
 '# 2026 AI 产品行业观察：Agent、RAG 与工作流融合趋势

## 趋势判断

- Agent 正从概念走向任务执行
- RAG 正从"能回答"走向"可运营"
- 工作流正在成为 AI 产品落地的主战场

## 产品机会

1. 面向高频垂类任务的 Copilot
2. 面向企业知识资产的检索与问答
3. 面向流程协同的自动化助手

## 结论

未来 AI 产品比拼的不是单点模型能力，而是把能力嵌进业务流程的能力。',
 '["行业观察", "Agent"]', 4),

('book-note-llm', '书摘：关于大模型产品的三层认知', '2026-05-29', 5,
 '从书籍内容提炼成适合产品经理复用的认知框架。',
 '# 书摘：关于大模型产品的三层认知

## 第一层：能力认知

先理解模型能做什么，不能做什么。

## 第二层：场景认知

再思考这个能力在哪些场景里有价值。

## 第三层：组织认知

最后考虑如何让团队真正用起来并形成复用。

## 一句话总结

大模型产品的核心，不是"会不会用模型"，而是"能不能把模型变成业务结果"。',
 '["书摘", "认知"]', 5),

('methodology-note', '产品方法论：如何写一份可执行的 AI PRD', '2026-05-20', 6,
 '将需求、约束、流程和验收标准写清楚，降低沟通成本。',
 '# 产品方法论：如何写一份可执行的 AI PRD

## 必写项

- 背景和目标
- 用户与使用场景
- 功能流程
- 约束与边界
- 数据埋点和验收标准

## 经验总结

PRD 的价值不在于写得长，而在于写得让研发、设计、算法和业务都能一致理解。',
 '["PRD", "方法论"]', 6);

-- 关于我 — 基础配置
INSERT INTO about_info (id, plan, evaluation, tools) VALUES
(1,
 '持续深耕 AI 应用落地、Agent 产品和商业化增长，成为既懂模型又懂业务的产品负责人',
 '结构化、结果导向、能沟通、能落地，擅长把复杂问题变成可执行方案。',
 '["Axure", "XMind", "Figma", "Notion", "飞书文档", "墨刀", "各类大模型"]');

-- 教育经历
INSERT INTO about_education (content, sort_order) VALUES
('XX 大学 · XX 专业 · 本科', 1),
('XX 大学 · XX 专业 · 硕士', 2);

-- 工作经历
INSERT INTO about_work (content, sort_order) VALUES
('XX 公司 AI 产品经理：主导企业知识库 AI 助手从 0 到 1 落地，检索效率提升 60%', 1),
('XX 公司产品经理：负责多模态内容审核系统需求拆解与上线，人工审核时长缩短 40%', 2),
('XX 公司业务分析：搭建数据看板与转化漏斗，支撑产品增长决策', 3);
