<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import Link from '@tiptap/extension-link';
import { Bold, Italic, List, ListOrdered, Undo, Redo, Link as LinkIcon, Unlink } from 'lucide-vue-next';

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Tulis sesuatu...' },
  minHeight: { type: String, default: '120px' },
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Placeholder.configure({ placeholder: props.placeholder }),
    Link.configure({
      openOnClick: false,
      HTMLAttributes: { class: 'text-primary underline cursor-pointer' },
    }),
  ],
  editorProps: {
    attributes: {
      class: 'prose prose-sm max-w-none focus:outline-none min-h-[var(--min-height)] p-4',
      style: `--min-height: ${props.minHeight}`,
    },
  },
  onUpdate: () => {
    emit('update:modelValue', editor.value?.getHTML() || '');
  },
});

watch(() => props.modelValue, (value) => {
  if (editor.value && editor.value.getHTML() !== value) {
    editor.value.commands.setContent(value || '', false);
  }
});

onBeforeUnmount(() => {
  editor.value?.destroy();
});

const setLink = () => {
  const previousUrl = editor.value?.getAttributes('link').href;
  const url = window.prompt('URL', previousUrl || 'https://');

  if (url === null) return;
  if (url === '') {
    editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
    return;
  }

  editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

const unsetLink = () => {
  editor.value?.chain().focus().unsetLink().run();
};

const toolbarButtons = [
  { action: () => editor.value?.chain().focus().toggleBold().run(), icon: Bold, active: () => editor.value?.isActive('bold'), title: 'Bold' },
  { action: () => editor.value?.chain().focus().toggleItalic().run(), icon: Italic, active: () => editor.value?.isActive('italic'), title: 'Italic' },
  { action: () => editor.value?.chain().focus().toggleBulletList().run(), icon: List, active: () => editor.value?.isActive('bulletList'), title: 'Bullet List' },
  { action: () => editor.value?.chain().focus().toggleOrderedList().run(), icon: ListOrdered, active: () => editor.value?.isActive('orderedList'), title: 'Numbered List' },
  { action: setLink, icon: LinkIcon, active: () => editor.value?.isActive('link'), title: 'Add Link' },
  { action: unsetLink, icon: Unlink, active: () => false, title: 'Remove Link', show: () => editor.value?.isActive('link') },
  { action: () => editor.value?.chain().focus().undo().run(), icon: Undo, active: () => false, title: 'Undo' },
  { action: () => editor.value?.chain().focus().redo().run(), icon: Redo, active: () => false, title: 'Redo' },
];
</script>

<template>
  <div
    class="border border-slate-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary/30">
    <!-- Toolbar -->
    <div class="flex items-center gap-1 p-2 bg-slate-50 border-b border-slate-200">
      <template v-for="btn in toolbarButtons" :key="btn.title">
        <button v-if="btn.show === undefined || btn.show()" type="button" @click="btn.action" :title="btn.title"
          class="p-1.5 rounded hover:bg-white transition-colors cursor-pointer"
          :class="btn.active() ? 'bg-white text-primary shadow-sm' : 'text-slate-500'">
          <component :is="btn.icon" class="w-4 h-4" />
        </button>
      </template>
    </div>

    <!-- Editor Content -->
    <EditorContent :editor="editor" class="bg-white" />
  </div>
</template>

<style>
.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #9ca3af;
  pointer-events: none;
  height: 0;
}

.ProseMirror:focus {
  outline: none;
}

.ProseMirror ul {
  list-style-type: disc;
  padding-left: 1.5rem;
}

.ProseMirror ol {
  list-style-type: decimal;
  padding-left: 1.5rem;
}

.ProseMirror p {
  margin-bottom: 0.5rem;
}

.ProseMirror a {
  color: var(--color-primary, #3b82f6);
  text-decoration: underline;
  cursor: pointer;
}
</style>
