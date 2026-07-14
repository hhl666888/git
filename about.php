<?php
/**
 * ============================================================
 * 关于我页面 — about.php
 * ============================================================
 * 动态渲染：教育经历、工作经历、职业规划、自我评价、工具标签
 * 编辑模式下：所有区块带"编辑/删除"按钮，顶部带"+ 新增"
 * ============================================================
 */

$pageTitle = 'AI产品经理作品集 | 关于我';
require_once __DIR__ . '/includes/header.php';

$about   = getAboutData();
$editing = isEditMode();

// 把单行配置组装成 about 实体的数据（用于编辑器）
$aboutSingletonData = [
    'plan'       => $about['plan'],
    'evaluation'=> $about['evaluation'],
    'tools'     => $about['tools'],
];
?>

<main class="shell section-block">
    <!-- 页面标题区 -->
    <section class="section-card page-hero"<?php if ($editing): ?> data-edit-type="profile" data-edit-id="1"<?= editDataAttr($profile) ?><?php endif; ?>>
        <?php if ($editing): ?><?= editCardBtns(1) ?><?php endif; ?>
        <p class="section-kicker">关于我</p>
        <h1>个人简介与职业经历</h1>
        <p><?= e($profile['intro']) ?></p>
    </section>

    <!-- 教育与工作经历 -->
    <div class="about-grid">
        <!-- 教育经历 -->
        <article class="section-card info-card">
            <p class="section-kicker">教育经历</p>
            <h2>学历背景</h2>
            <?php if ($editing): ?><?= editAddBtn('education', '+ 新增教育经历') ?><?php endif; ?>
            <?php if (empty($about['education'])): ?>
                <p>暂无教育经历数据。</p>
            <?php else: ?>
                <?php foreach ($about['education'] as $edu): ?>
                    <p<?php if ($editing): ?> data-edit-type="education" data-edit-id="<?= (int)$edu['id'] ?>"<?= editDataAttr($edu) ?> style="position:relative; padding-right:80px;"<?php endif; ?>>
                        <?= e($edu['content']) ?>
                        <?php if ($editing): ?><?= editCardBtns($edu['id']) ?><?php endif; ?>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>
        </article>

        <!-- 工作经历 -->
        <article class="section-card info-card">
            <p class="section-kicker">工作经历</p>
            <h2>职业路径</h2>
            <?php if ($editing): ?><?= editAddBtn('work', '+ 新增工作经历') ?><?php endif; ?>
            <?php if (empty($about['work'])): ?>
                <p>暂无工作经历数据。</p>
            <?php else: ?>
                <?php foreach ($about['work'] as $work): ?>
                    <p<?php if ($editing): ?> data-edit-type="work" data-edit-id="<?= (int)$work['id'] ?>"<?= editDataAttr($work) ?> style="position:relative; padding-right:80px;"<?php endif; ?>>
                        <?= e($work['content']) ?>
                        <?php if ($editing): ?><?= editCardBtns($work['id']) ?><?php endif; ?>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>
        </article>
    </div>

    <!-- 职业规划 + 自我评价（两栏） -->
    <div class="about-grid">
        <section class="section-card info-card"<?php if ($editing): ?> data-edit-type="about" data-edit-id="1"<?= editDataAttr($aboutSingletonData) ?> style="position:relative;"<?php endif; ?>>
            <?php if ($editing): ?><?= editCardBtns(1) ?><?php endif; ?>
            <p class="section-kicker">职业规划</p>
            <h2>未来方向</h2>
            <p><?= e($about['plan']) ?></p>
        </section>

        <section class="section-card info-card"<?php if ($editing): ?> data-edit-type="about" data-edit-id="1"<?= editDataAttr($aboutSingletonData) ?> style="position:relative;"<?php endif; ?>>
            <?php if ($editing): ?><?= editCardBtns(1) ?><?php endif; ?>
            <p class="section-kicker">自我评价</p>
            <h2>能力特点</h2>
            <p><?= e($about['evaluation']) ?></p>
        </section>
    </div>

    <!-- 工具标签（全宽） -->
    <section class="section-card info-card"<?php if ($editing): ?> data-edit-type="about" data-edit-id="1"<?= editDataAttr($aboutSingletonData) ?> style="position:relative;"<?php endif; ?>>
        <?php if ($editing): ?><?= editCardBtns(1) ?><?php endif; ?>
        <p class="section-kicker">常用工具</p>
        <h2>工具与方法论</h2>
        <?php if (empty($about['tools'])): ?>
            <p>暂无工具标签。</p>
        <?php else: ?>
            <div class="badge-row">
                <?php foreach ($about['tools'] as $tool): ?>
                    <span class="badge"><?= e($tool) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
