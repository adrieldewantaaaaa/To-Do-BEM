<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
const props = defineProps({ files: Object, filters: Object });
const form = reactive({ search: props.filters.search ?? '' });
let timer;
const search = () => {
  clearTimeout(timer);
  timer = setTimeout(() => router.get(route('files'), form, { preserveState: true, replace: true }), 300);
};
</script>
<template>
  <AppLayout title="Files"
    ><div class="mb-7">
      <h1 class="tdb-heading text-3xl sm:text-4xl">Files</h1>
      <p class="mt-2 text-[var(--muted)]">Every task attachment, traceable to its project.</p>
    </div>
    <div class="tdb-panel mb-5 p-4">
      <div class="relative max-w-xl">
        <TdbIcon name="search" :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--muted)]" /><input
          v-model="form.search"
          class="tdb-input !pl-10"
          type="search"
          placeholder="Search filenames…"
          aria-label="Search files"
          @input="search"
        />
      </div>
    </div>
    <section class="tdb-card overflow-hidden">
      <div v-if="files.data.length" class="divide-y divide-[var(--line)]">
        <div v-for="file in files.data" :key="file.id" class="flex items-center gap-3 px-4 py-4 sm:px-5">
          <span class="grid h-11 w-11 flex-none place-items-center rounded-[11px_8px_12px_9px] bg-[var(--surface-2)]"
            ><TdbIcon name="paperclip" :size="20"
          /></span>
          <div class="min-w-0 flex-1">
            <strong class="block truncate">{{ file.original_name }}</strong>
            <p class="mt-1 truncate text-xs text-[var(--muted)]">
              {{ file.task.project.name }} · {{ file.task.title }} · {{ file.human_size }}
            </p>
          </div>
          <a :href="route('attachments.download', file.id)" class="tdb-btn tdb-btn-secondary !min-h-9 !px-3"
            ><TdbIcon name="download" :size="17" /><span class="hide-mobile">Download</span></a
          >
        </div>
      </div>
      <div v-else class="px-5 py-16 text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-xl border border-dashed border-[var(--line)]"
          ><TdbIcon name="paperclip" :size="27"
        /></span>
        <h2 class="tdb-heading mt-4 text-2xl">No files found.</h2>
        <p class="mt-2 text-sm text-[var(--muted)]">Attachments uploaded to tasks will appear here.</p>
      </div>
    </section>
    <nav v-if="files.links.length > 3" class="mt-6 flex flex-wrap justify-center gap-1">
      <Link
        v-for="link in files.links"
        :key="link.label"
        :href="link.url || '#'"
        class="tdb-btn tdb-btn-secondary !min-h-9 !px-3"
        :class="{ '!bg-[var(--primary)] !text-white': link.active, 'pointer-events-none opacity-45': !link.url }"
        v-html="link.label"
      /></nav
  ></AppLayout>
</template>
