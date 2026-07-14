/**
 * ============================================================
 * 前端编辑器 — assets/js/editor.js
 * ============================================================
 * 功能：
 *   - SCHEMA 驱动的模态框表单生成
 *   - 标签输入组件（× 删除、+ 新增、回车确认）
 *   - JSON 数组列表组件（多条文本，逐条增删）
 *   - AJAX 请求封装（含 loading 提示）
 *   - 事件委托：编辑/删除/新增按钮、内联可编辑文本
 *
 * 使用约定：
 *   PHP 端在编辑模式下渲染以下数据属性，JS 自动绑定事件：
 *     - data-edit-type="project"  实体类型，对应 SCHEMA key
 *     - data-edit-id="123"        实体 ID
 *     - data-edit-data='{"name": "..."}'  实体完整数据（JSON）
 *     - .edit-card-btns           卡片右上角的编辑/删除按钮容器
 *     - .edit-add-btn             列表顶部的"+ 新增"按钮
 *     - .edit-inline[data-edit-field]  内联可编辑文本
 * ============================================================
 */

(function () {
'use strict';

// ============================================================
//  SCHEMA 定义：每个实体类型的字段配置
// ============================================================

const SCHEMA = {
  // ---------- 个人信息（单行配置） ----------
  profile: {
    title: '编辑个人信息',
    action: 'update_profile',
    singleton: true,  // 单行记录，不需要 ID
    fields: [
      { name: 'name',   label: '姓名',  type: 'text' },
      { name: 'slogan', label: '标语',  type: 'text' },
      { name: 'intro',  label: '简介',  type: 'textarea' },
      { name: 'badges', label: '首页标签', type: 'tags' },
      { name: 'github', label: 'GitHub 地址', type: 'text' },
      { name: 'email',  label: '邮箱',  type: 'text' },
      { name: 'resume', label: '简历文件路径', type: 'text' },
    ],
  },

  // ---------- 关于我（单行配置） ----------
  about: {
    title: '编辑关于我',
    action: 'update_about',
    singleton: true,
    fields: [
      { name: 'plan',       label: '职业规划', type: 'textarea' },
      { name: 'evaluation', label: '自我评价', type: 'textarea' },
      { name: 'tools',      label: '常用工具', type: 'tags' },
    ],
  },

  // ---------- 首页数据卡片 ----------
  stat: {
    title: '数据卡片',
    addAction: 'add_stat',
    editAction: 'edit_stat',
    deleteAction: 'delete_stat',
    fields: [
      { name: 'value',      label: '显示数值', type: 'text',   placeholder: '如 5+' },
      { name: 'label',      label: '描述文字', type: 'text' },
      { name: 'sort_order', label: '排序（越小越靠前）', type: 'number', default: 0 },
    ],
  },

  // ---------- 核心能力 ----------
  capability: {
    title: '核心能力',
    addAction: 'add_capability',
    editAction: 'edit_capability',
    deleteAction: 'delete_capability',
    fields: [
      { name: 'title',       label: '能力标题', type: 'text' },
      { name: 'description', label: '能力描述', type: 'textarea' },
      { name: 'sort_order',  label: '排序',     type: 'number', default: 0 },
    ],
  },

  // ---------- 项目 ----------
  project: {
    title: '项目',
    addAction: 'add_project',
    editAction: 'edit_project',
    deleteAction: 'delete_project',
    fields: [
      { name: 'slug',  label: 'URL 标识（英文）', type: 'text', placeholder: '如 ai-assistant' },
      { name: 'name',  label: '项目名称', type: 'text' },
      { name: 'type',  label: '项目类型', type: 'text' },
      { name: 'intro', label: '项目简介', type: 'textarea' },
      { name: 'role',  label: '我的职责', type: 'text' },
      { name: 'result', label: '成果数据', type: 'text' },
      { name: 'cover', label: '封面文字', type: 'text' },
      { name: 'github', label: 'GitHub 仓库地址', type: 'text' },
      { name: 'tags',  label: '项目标签', type: 'tags' },
      { name: 'detail_background', label: '项目背景', type: 'textarea' },
      { name: 'detail_prd',        label: 'PRD 要点（多条）', type: 'list' },
      { name: 'detail_screenshots', label: '截图说明（多条）', type: 'list' },
      { name: 'detail_metrics',   label: '核心指标（多条）', type: 'list' },
      { name: 'detail_review',     label: '复盘总结', type: 'textarea' },
      { name: 'sort_order', label: '排序', type: 'number', default: 0 },
    ],
  },

  // ---------- 笔记 ----------
  note: {
    title: '笔记',
    addAction: 'add_note',
    editAction: 'edit_note',
    deleteAction: 'delete_note',
    fields: [
      { name: 'slug',  label: 'URL 标识（英文）', type: 'text', placeholder: '如 llm-thinking' },
      { name: 'title', label: '笔记标题', type: 'text' },
      { name: 'date',  label: '发布日期', type: 'date' },
      { name: 'category_id', label: '分类', type: 'category-select' },
      { name: 'summary', label: '摘要', type: 'textarea' },
      { name: 'content', label: '正文（支持 Markdown）', type: 'markdown' },
      { name: 'tags',   label: '标签', type: 'tags' },
      { name: 'sort_order', label: '排序', type: 'number', default: 0 },
    ],
  },

  // ---------- 笔记分类 ----------
  category: {
    title: '笔记分类',
    addAction: 'add_category',
    editAction: 'edit_category',
    deleteAction: 'delete_category',
    fields: [
      { name: 'name',       label: '分类名称', type: 'text' },
      { name: 'sort_order', label: '排序',     type: 'number', default: 0 },
    ],
  },

  // ---------- 教育经历 ----------
  education: {
    title: '教育经历',
    addAction: 'add_education',
    editAction: 'edit_education',
    deleteAction: 'delete_education',
    fields: [
      { name: 'content',    label: '经历描述', type: 'text' },
      { name: 'sort_order', label: '排序',     type: 'number', default: 0 },
    ],
  },

  // ---------- 工作经历 ----------
  work: {
    title: '工作经历',
    addAction: 'add_work',
    editAction: 'edit_work',
    deleteAction: 'delete_work',
    fields: [
      { name: 'content',    label: '经历描述', type: 'text' },
      { name: 'sort_order', label: '排序',     type: 'number', default: 0 },
    ],
  },
};

// ============================================================
//  DOM 辅助
// ============================================================

function h(tag, attrs, children) {
  const el = document.createElement(tag);
  if (attrs) {
    for (const key in attrs) {
      if (key === 'class') el.className = attrs[key];
      else if (key === 'text') el.textContent = attrs[key];
      else if (key === 'html') el.innerHTML = attrs[key];
      else if (key.startsWith('on') && typeof attrs[key] === 'function') {
        el.addEventListener(key.slice(2).toLowerCase(), attrs[key]);
      } else if (attrs[key] !== null && attrs[key] !== undefined) {
        el.setAttribute(key, attrs[key]);
      }
    }
  }
  if (children) {
    if (!Array.isArray(children)) children = [children];
    children.forEach(c => {
      if (c == null) return;
      el.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
    });
  }
  return el;
}

// ============================================================
//  AJAX 封装
// ============================================================

function apiCall(action, payload) {
  return fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(Object.assign({ action }, payload)),
  }).then(r => r.json()).then(res => {
    if (!res.ok) throw new Error(res.error || '请求失败');
    return res.data;
  });
}

// ============================================================
//  模态框
// ============================================================

let activeModal = null;

function closeModal() {
  if (activeModal) {
    activeModal.remove();
    activeModal = null;
    document.removeEventListener('keydown', onModalKeydown);
  }
}

function onModalKeydown(e) {
  if (e.key === 'Escape') closeModal();
}

/**
 * 打开模态框
 * @param {string} type         实体类型，对应 SCHEMA key
 * @param {object|null} data    现有数据（编辑模式）或 null（新增模式）
 * @param {function} onSaved    保存成功后的回调
 */
function openModal(type, data, onSaved) {
  closeModal();
  const schema = SCHEMA[type];
  if (!schema) {
    console.error('未知实体类型:', type);
    return;
  }
  const isEdit = !!data;
  const title = (isEdit ? '编辑' : '新增') + schema.title;
  const mode = isEdit ? 'edit' : 'add';

  // 表单状态：所有字段的当前值
  const state = {};
  schema.fields.forEach(f => {
    if (data && data[f.name] !== undefined) {
      state[f.name] = data[f.name];
    } else if (f.default !== undefined) {
      state[f.name] = f.default;
    } else if (f.type === 'tags' || f.type === 'list') {
      state[f.name] = [];
    } else if (f.type === 'number') {
      state[f.name] = 0;
    } else if (f.type === 'date') {
      state[f.name] = new Date().toISOString().slice(0, 10);
    } else {
      state[f.name] = '';
    }
  });

  // 构建模态框 DOM
  const overlay = h('div', { class: 'edit-modal-overlay' });
  const modal = h('div', { class: 'edit-modal' + (schema.fields.length <= 3 ? ' edit-modal-sm' : '') });
  const head = h('div', { class: 'edit-modal-head' }, h('h3', { text: title }));
  const body = h('div', { class: 'edit-modal-body' });
  const foot = h('div', { class: 'edit-modal-foot' });

  // 渲染每个字段
  schema.fields.forEach(field => {
    const group = h('div', { class: 'edit-form-group' });
    group.appendChild(h('label', { class: 'edit-form-label', text: field.label }));
    const input = renderField(field, state[field.name], val => { state[field.name] = val; });
    group.appendChild(input);
    body.appendChild(group);
  });

  // 底部按钮
  const cancelBtn = h('button', {
    class: 'button button-ghost',
    type: 'button',
    text: '取消',
    onclick: closeModal,
  });
  const saveBtn = h('button', {
    class: 'button button-primary',
    type: 'button',
    text: '保存',
    onclick: () => saveModal(schema, state, mode, data, saveBtn, onSaved),
  });
  foot.appendChild(cancelBtn);
  foot.appendChild(saveBtn);

  modal.appendChild(head);
  modal.appendChild(body);
  modal.appendChild(foot);
  overlay.appendChild(modal);
  overlay.addEventListener('click', e => {
    if (e.target === overlay) closeModal();
  });
  document.body.appendChild(overlay);
  document.addEventListener('keydown', onModalKeydown);
  activeModal = overlay;

  // 自动聚焦第一个输入框
  setTimeout(() => {
    const firstInput = body.querySelector('input, textarea');
    if (firstInput) firstInput.focus();
  }, 0);
}

/**
 * 保存模态框数据
 */
function saveModal(schema, state, mode, existingData, btn, onSaved) {
  btn.disabled = true;
  const originalText = btn.textContent;
  btn.textContent = '保存中...';

  const action = mode === 'add' ? schema.addAction : schema.editAction;
  const payload = { action, data: state };
  if (mode === 'edit' && existingData) {
    payload.id = existingData.id || existingData._id || 0;
  }

  apiCall(action, payload)
    .then(() => {
      closeModal();
      if (onSaved) onSaved();
      else window.location.reload();
    })
    .catch(err => {
      btn.disabled = false;
      btn.textContent = originalText;
      alert('保存失败：' + err.message);
    });
}

// ============================================================
//  字段渲染器
// ============================================================

function renderField(field, value, onChange) {
  switch (field.type) {
    case 'text':     return renderTextInput(field, value, onChange);
    case 'number':   return renderNumberInput(field, value, onChange);
    case 'date':     return renderDateInput(field, value, onChange);
    case 'textarea': return renderTextarea(field, value, onChange);
    case 'markdown': return renderMarkdown(field, value, onChange);
    case 'tags':     return renderTagsInput(field, value, onChange);
    case 'list':     return renderListInput(field, value, onChange);
    case 'category-select': return renderCategorySelect(field, value, onChange);
    default:
      return renderTextInput(field, value, onChange);
  }
}

function renderTextInput(field, value, onChange) {
  const input = h('input', {
    class: 'edit-form-input',
    type: 'text',
    value: value || '',
    placeholder: field.placeholder || '',
    oninput: e => onChange(e.target.value),
  });
  return input;
}

function renderNumberInput(field, value, onChange) {
  const input = h('input', {
    class: 'edit-form-input',
    type: 'number',
    value: value ?? 0,
    oninput: e => onChange(parseInt(e.target.value, 10) || 0),
  });
  return input;
}

function renderDateInput(field, value, onChange) {
  const input = h('input', {
    class: 'edit-form-input',
    type: 'date',
    value: value || '',
    oninput: e => onChange(e.target.value),
  });
  return input;
}

function renderTextarea(field, value, onChange) {
  const ta = h('textarea', {
    class: 'edit-form-input edit-form-textarea',
    placeholder: field.placeholder || '',
    oninput: e => onChange(e.target.value),
  });
  ta.value = value || '';
  return ta;
}

function renderMarkdown(field, value, onChange) {
  const wrap = h('div');
  const ta = h('textarea', {
    class: 'edit-form-input edit-form-textarea edit-form-markdown',
    placeholder: '支持 Markdown 语法',
    oninput: e => onChange(e.target.value),
  });
  ta.value = value || '';
  wrap.appendChild(ta);
  return wrap;
}

// ============================================================
//  标签输入组件（自由文本）
// ============================================================

function renderTagsInput(field, value, onChange) {
  const tags = Array.isArray(value) ? [...value] : [];
  const wrap = h('div', { class: 'edit-multiselect' });

  function render() {
    wrap.innerHTML = '';
    tags.forEach((tag, idx) => {
      const pill = h('button', {
        class: 'edit-multiselect-option active',
        type: 'button',
      });
      pill.appendChild(document.createTextNode(tag));
      const del = h('span', {
        text: '×',
        style: 'margin-left:6px; font-weight:700; opacity:0.7;',
        onclick: (e) => {
          e.preventDefault();
          tags.splice(idx, 1);
          onChange([...tags]);
          render();
        },
      });
      pill.appendChild(del);
      wrap.appendChild(pill);
    });
    // 新增输入框
    const add = h('input', {
      type: 'text',
      placeholder: '+ 输入标签后回车，空格分隔可一次添加多个',
      style: 'flex:1; min-width:120px; border:none; outline:none; background:transparent; padding:0 8px; font-size:14px;',
      onkeydown: (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const parts = e.target.value.trim().split(/\s+/).filter(Boolean);
          parts.forEach(v => {
            if (v && !tags.includes(v)) {
              tags.push(v);
            }
          });
          if (parts.length > 0) {
            onChange([...tags]);
            render();
          }
          e.target.value = '';
        }
      },
    });
    wrap.appendChild(add);
    setTimeout(() => add.focus(), 0);
  }

  render();
  return wrap;
}

// ============================================================
//  列表输入组件（多条文本）
// ============================================================

function renderListInput(field, value, onChange) {
  const items = Array.isArray(value) ? [...value] : [];
  const wrap = h('div', { class: 'edit-list-input', style: 'display:flex; flex-direction:column; gap:8px;' });

  function render() {
    wrap.innerHTML = '';
    items.forEach((item, idx) => {
      const row = h('div', { style: 'display:flex; gap:8px; align-items:center;' });
      const input = h('input', {
        type: 'text',
        class: 'edit-form-input',
        value: item,
        placeholder: '输入一条内容',
        oninput: (e) => {
          items[idx] = e.target.value;
          onChange([...items]);
        },
      });
      const delBtn = h('button', {
        class: 'button button-ghost',
        type: 'button',
        text: '删除',
        style: 'flex-shrink:0;',
        onclick: () => {
          items.splice(idx, 1);
          onChange([...items]);
          render();
        },
      });
      row.appendChild(input);
      row.appendChild(delBtn);
      wrap.appendChild(row);
    });
    // "+ 添加一条" 按钮
    const addBtn = h('button', {
      class: 'edit-badge-add',
      type: 'button',
      text: '+ 添加一条',
      style: 'align-self:flex-start;',
      onclick: () => {
        items.push('');
        onChange([...items]);
        render();
      },
    });
    wrap.appendChild(addBtn);
  }

  render();
  return wrap;
}

// ============================================================
//  分类下拉选择
// ============================================================

function renderCategorySelect(field, value, onChange) {
  const wrap = h('div');
  const select = h('select', {
    class: 'edit-form-input',
    onchange: e => onChange(parseInt(e.target.value, 10) || 0),
  });
  // 从页面注入的全局变量读取分类列表（编辑模式下 footer.php 注入）
  let categories = [];
  if (window.__NOTE_CATEGORIES__ && Array.isArray(window.__NOTE_CATEGORIES__)) {
    categories = window.__NOTE_CATEGORIES__;
  } else {
    // 回退：从侧边栏 filter-pill 读取（仅作展示，id 不可靠）
    document.querySelectorAll('.notes-sidebar .filter-pill').forEach(p => {
      const name = p.textContent.trim();
      if (name && name !== '全部') {
        categories.push({ id: 0, name });
      }
    });
  }
  // 空分类提示
  if (categories.length === 0) {
    const opt = h('option', { value: 0, text: '暂无分类，请先创建分类' });
    select.appendChild(opt);
  } else {
    categories.forEach(cat => {
      const opt = h('option', { value: cat.id, text: cat.name });
      if (parseInt(cat.id, 10) === parseInt(value, 10)) {
        opt.setAttribute('selected', 'selected');
      }
      select.appendChild(opt);
    });
  }
  wrap.appendChild(select);
  // 若分类 id 全部为 0（从 DOM 回退读取），提示用户
  if (categories.length > 0 && categories.every(c => !c.id)) {
    const tip = h('p', {
      class: 'edit-form-tip',
      style: 'color:#e74c3c;font-size:12px;margin-top:4px;',
      text: '提示：请先在笔记列表页创建分类后再新增/编辑笔记',
    });
    wrap.appendChild(tip);
  }
  return wrap;
}

// ============================================================
//  事件绑定
// ============================================================

function initEditor() {
  // 编辑按钮：data-edit-type + data-edit-id
  document.body.addEventListener('click', (e) => {
    const editBtn = e.target.closest('[data-edit-action="edit"]');
    if (editBtn) {
      e.preventDefault();
      const card = editBtn.closest('[data-edit-type]');
      if (!card) return;
      const type = card.dataset.editType;
      let data = {};
      try {
        data = JSON.parse(card.dataset.editData || '{}');
      } catch (err) { data = {}; }
      data.id = parseInt(card.dataset.editId, 10) || 0;
      openModal(type, data, () => window.location.reload());
      return;
    }

    // 删除按钮
    const delBtn = e.target.closest('[data-edit-action="delete"]');
    if (delBtn) {
      e.preventDefault();
      e.stopPropagation();
      const card = delBtn.closest('[data-edit-type]');
      if (!card) return;
      const type = card.dataset.editType;
      const id = parseInt(card.dataset.editId, 10) || 0;
      const schema = SCHEMA[type];
      if (!schema || !schema.deleteAction) return;
      if (!confirm('确认删除？此操作不可恢复。')) return;
      apiCall(schema.deleteAction, { id }).then(() => {
        window.location.reload();
      }).catch(err => {
        alert('删除失败：' + err.message);
      });
      return;
    }

    // 新增按钮
    const addBtn = e.target.closest('[data-edit-action="add"]');
    if (addBtn) {
      e.preventDefault();
      const type = addBtn.dataset.editType;
      openModal(type, null, () => window.location.reload());
      return;
    }

    // 整体可点击的实体（如分类侧边栏的 filter-pill）
    // 仅当 target 直接命中带 data-edit-type 的元素、且没有更具体的子按钮被点击时触发
    const clickable = e.target.closest('[data-edit-type]');
    if (clickable
        && !e.target.closest('[data-edit-action]')
        && !e.target.closest('a')
        && !e.target.closest('button:not(.filter-pill)')
        && clickable.tagName !== 'A'
        && clickable.tagName !== 'BUTTON') {
      // 排除 stat-card / capability-card / card / info-card 这些有独立编辑按钮的卡片
      // 仅对 filter-pill 这种整体可点击的场景生效
      if (clickable.classList.contains('filter-pill')) {
        e.preventDefault();
        const type = clickable.dataset.editType;
        let data = {};
        try {
          data = JSON.parse(clickable.dataset.editData || '{}');
        } catch (err) { data = {}; }
        data.id = parseInt(clickable.dataset.editId, 10) || 0;
        openModal(type, data, () => window.location.reload());
        return;
      }
    }

    // 编辑模式切换：进入
    const enterBtn = e.target.closest('#edit-enter-btn');
    if (enterBtn) {
      e.preventDefault();
      apiCall('set_edit_mode', { data: { mode: true } }).then(() => {
        // 跳转到当前页面（带 edit=1 触发 PHP 端识别）
        const url = new URL(window.location.href);
        url.searchParams.set('edit', '1');
        window.location.href = url.toString();
      });
      return;
    }

    // 编辑模式切换：退出
    const exitBtn = e.target.closest('#edit-exit-btn');
    if (exitBtn) {
      e.preventDefault();
      apiCall('set_edit_mode', { data: { mode: false } }).then(() => {
        const url = new URL(window.location.href);
        url.searchParams.delete('edit');
        url.searchParams.delete('edit_action');
        window.location.href = url.toString();
      });
      return;
    }
  });

  // 内联可编辑文本：双击编辑（contenteditable）
  document.body.addEventListener('dblclick', (e) => {
    const inline = e.target.closest('.edit-inline[data-edit-field]');
    if (!inline) return;
    const card = inline.closest('[data-edit-type]');
    if (!card) return;
    const type = card.dataset.editType;
    const field = inline.dataset.editField;
    const schema = SCHEMA[type];
    if (!schema) return;
    // 打开完整模态框
    let data = {};
    try { data = JSON.parse(card.dataset.editData || '{}'); } catch (err) {}
    data.id = parseInt(card.dataset.editId, 10) || 0;
    openModal(type, data, () => window.location.reload());
  });
}

// ============================================================
//  初始化
// ============================================================

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initEditor);
} else {
  initEditor();
}

// 暴露给外部调用
window.__EDITOR__ = { openModal, closeModal, SCHEMA, apiCall };

})();
