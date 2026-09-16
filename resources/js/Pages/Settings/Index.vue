<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
const props = defineProps({ appearance: String });
const form = useForm({ appearance: props.appearance });
const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });

const updatePassword = () => {
  passwordForm.patch(route('settings.password'), {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  });
};
const options = [
  { key: 'light', label: 'Light', description: 'Paper background with crisp ink.', symbol: '☀' },
  { key: 'dark', label: 'Dark', description: 'Charcoal surfaces with soft contrast.', symbol: '◐' },
  { key: 'system', label: 'System', description: 'Follow this device automatically.', symbol: '◒' },
];
const preview = (choice) => {
  form.appearance = choice;
  localStorage.setItem('tdb-appearance', choice);
  const dark = choice === 'dark' || (choice === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
  document.documentElement.classList.toggle('dark', dark);
};
const submit = () => form.patch(route('settings.appearance'), { preserveScroll: true });
</script>
<template>
  <AppLayout title="Settings"
    ><div class="mx-auto max-w-4xl">
      <div class="mb-7">
        <h1 class="tdb-heading text-3xl sm:text-4xl">{{ 'Settings' }}</h1>
        <p class="mt-2 text-[var(--muted)]">Tune the workspace without adding visual noise.</p>
      </div>
      <section class="tdb-card overflow-hidden">
        <header class="border-b border-[var(--line)] px-5 py-4 sm:px-6">
          <h2 class="text-lg font-extrabold">{{ 'Account Settings' }}</h2>
          <p class="mt-1 text-sm text-[var(--muted)]">Manage your account preferences here.</p>
        </header>
        <div class="p-5 sm:p-6 text-sm text-[var(--muted)] border-b border-[var(--line)]">
          <p>Logged in as: <strong>{{ $page.props.auth.user.name }}</strong> ({{ $page.props.auth.user.email }})</p>
        </div>
        <form class="p-5 sm:p-6" @submit.prevent="updatePassword">
          <h3 class="font-bold mb-4">{{ 'Change Password' }}</h3>
          <div class="grid gap-4 max-w-sm">
            <div>
              <label class="block mb-1">{{ 'Current Password' }}</label>
              <input type="password" v-model="passwordForm.current_password" class="tdb-input w-full" />
              <div v-if="passwordForm.errors.current_password" class="text-[var(--danger)] text-xs mt-1">{{ passwordForm.errors.current_password }}</div>
            </div>
            <div>
              <label class="block mb-1">{{ 'New Password' }}</label>
              <input type="password" v-model="passwordForm.password" class="tdb-input w-full" />
              <div v-if="passwordForm.errors.password" class="text-[var(--danger)] text-xs mt-1">{{ passwordForm.errors.password }}</div>
            </div>
            <div>
              <label class="block mb-1">{{ 'Confirm Password' }}</label>
              <input type="password" v-model="passwordForm.password_confirmation" class="tdb-input w-full" />
            </div>
            <div class="mt-2">
              <TdbButton type="submit" :disabled="passwordForm.processing">{{ passwordForm.processing ? 'Saving…' : 'Update password' }}</TdbButton>
            </div>
          </div>
        </form>
      </section>
      
      <section class="tdb-card overflow-hidden mt-5">
        <header class="border-b border-[var(--line)] px-5 py-4 sm:px-6">
          <h2 class="text-lg font-extrabold">{{ 'Appearance' }}</h2>
          <p class="mt-1 text-sm text-[var(--muted)]">Choose how TDB looks on this account.</p>
        </header>
        <form class="p-5 sm:p-6" @submit.prevent="submit">
          <fieldset>
            <legend class="sr-only">Color mode</legend>
            <div class="grid gap-4 sm:grid-cols-3">
              <label
                v-for="option in options"
                :key="option.key"
                class="relative cursor-pointer rounded-[13px_10px_14px_11px] border p-4 transition"
                :class="
                  form.appearance === option.key
                    ? 'sketch-selected border-[var(--primary)]'
                    : 'border-[var(--line)] hover:bg-[var(--surface-2)]'
                "
                ><input
                  class="sr-only"
                  type="radio"
                  name="appearance"
                  :value="option.key"
                  :checked="form.appearance === option.key"
                  @change="preview(option.key)" /><span
                  class="mb-4 grid h-10 w-10 place-items-center rounded-lg border border-[var(--line)] bg-[var(--surface-2)] text-xl"
                  aria-hidden="true"
                  >{{ option.symbol }}</span
                ><strong class="block">{{ option.label }}</strong
                ><span class="mt-1 block text-sm text-[var(--muted)]">{{ option.description }}</span
                ><span v-if="form.appearance === option.key" class="absolute right-3 top-3 text-[var(--primary)]"
                  ><TdbIcon name="check" :size="19" /></span
              ></label>
            </div>
          </fieldset>
          <div class="mt-6 flex justify-end border-t border-[var(--line)] pt-5">
            <TdbButton type="submit" :disabled="form.processing">{{
              form.processing ? 'Saving…' : 'Save preference'
            }}</TdbButton>
          </div>
        </form>
      </section>
    </div></AppLayout
  >
</template>
