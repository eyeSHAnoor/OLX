<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import Underline from '@tiptap/extension-underline';
import { Table } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableCell } from '@tiptap/extension-table-cell';
import { TableHeader } from '@tiptap/extension-table-header';
import { cn } from '@/lib/utils';

// Props same as your Input component
const props = defineProps<{
    defaultValue?: string | null;
    modelValue?: string | null;
    class?: HTMLAttributes['class'];
    id?: string;
    label?: string;
    error?: string;
    wrapperClass?: string;
    labelClass?: string;
    inputClass?: string;
    horizontal?: boolean;
    help?: string;
}>();

const emits = defineEmits<{ (e: 'update:modelValue', payload: string): void }>();

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
});

const editor = ref<Editor>();

editor.value = new Editor({
    content: modelValue.value || '',
    extensions: [
        StarterKit,
        Underline,
        Link.configure({ openOnClick: false }),
        Image,
        Table.configure({ resizable: true }),
        TableRow,
        TableHeader,
        TableCell,
    ],
    onUpdate: ({ editor }) => {
        modelValue.value = editor.getHTML();
    },
});

onBeforeUnmount(() => {
    editor.value?.destroy();
});
</script>

<template>
    <div :class="['flex', horizontal ? 'flex-row items-start gap-4' : 'flex-col', wrapperClass]">
        <!-- Label -->
        <Label v-if="label" :for="id" :class="[labelClass, horizontal ? 'mb-2 min-w-[100px]' : '']">
            {{ label }}
        </Label>

        <div v-if="editor" class="flex flex-1 flex-col border rounded-md">
            <!-- Toolbar -->
            <div class="flex flex-wrap gap-1 rounded-t-md border bg-muted/20 p-1">
                <Button
                    :variant="editor?.isActive('bold') ? 'secondary' : 'ghost'"
                    size="xs"
                    @click="editor?.chain().focus().toggleBold().run()"
                    :class="{ 'bg-accent': editor?.isActive('bold') }"
                >
                    B
                </Button>
                <Button :variant="editor?.isActive('italic') ? 'secondary' : 'ghost'" size="xs" @click="editor?.chain().focus().toggleItalic().run()"
                    ><i>I</i></Button
                >
                <Button
                    :variant="editor?.isActive('underline') ? 'secondary' : 'ghost'"
                    size="xs"
                    @click="editor?.chain().focus().toggleUnderline().run()"
                    >U
                </Button>
                <Button
                    :variant="editor.isActive('heading', { level: 2 }) ? 'secondary' : 'ghost'"
                    size="xs"
                    @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                    :class="{ 'bg-accent': editor.isActive('heading', { level: 2 }) }"
                    >H2
                </Button>
                <Button
                    :variant="editor.isActive('bulletList') ? 'secondary' : 'ghost'"
                    size="xs"
                    @click="editor?.chain().focus().toggleBulletList().run()"
                    :class="{ 'bg-accent': editor.isActive('bulletList') }"
                    >• List
                </Button>
                <Button
                    :variant="editor.isActive('orderedList') ? 'secondary' : 'ghost'"
                    size="xs"
                    @click="editor?.chain().focus().toggleOrderedList().run()"
                    :class="{ 'bg-accent': editor.isActive('orderedList') }"
                    >1. List
                </Button>
                <Button
                    :variant="editor.isActive('blockquote') ? 'secondary' : 'ghost'"
                    size="xs"
                    @click="editor?.chain().focus().toggleBlockquote().run()"
                    :class="{ 'bg-accent': editor.isActive('blockquote') }"
                    >❝
                </Button>
                <Button variant="ghost" size="xs" @click="editor?.chain().focus().setHorizontalRule().run()">―</Button>
                <Button
                    variant="ghost"
                    size="xs"
                    @click="
                        editor
                            ?.chain()
                            .focus()
                            .setImage({ src: prompt('Image URL') })
                            .run()
                    "
                    >🖼
                </Button>
                <Button variant="ghost" size="xs" @click="editor?.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()">
                    Table
                </Button>
                <Button variant="ghost" size="xs" @click="editor?.chain().focus().undo().run()">↺</Button>
                <Button variant="ghost" size="xs" @click="editor?.chain().focus().redo().run()">↻</Button>
            </div>

            <!-- Editor -->
            <EditorContent
                :editor="editor"
                :class="
                    cn(
                        'prose prose-sm min-h-[100px] max-w-none rounded-md border border-input px-3 py-2 text-sm leading-relaxed focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                        inputClass,
                    )
                "
            />
            <!-- Help/Error -->
            <div v-if="help || error" class="flex flex-col">
                <p v-if="help" class="text-light mt-2 ml-1 text-xs">{{ help }}</p>
                <InputError class="mt-2" :message="error" />
            </div>
        </div>
    </div>
</template>

<style>
.ProseMirror:focus {
    outline: none !important; /* blue outline remove */
    box-shadow: none !important; /* koi shadow bhi nahi */
}

.prose table {
    border-collapse: collapse;
    width: 100%;
    border: 1px solid #d1d5db; /* gray-300 */
}

.prose th,
.prose td {
    border: 1px solid #d1d5db;
    padding: 0.5rem; /* spacing-2 */
}
</style>
