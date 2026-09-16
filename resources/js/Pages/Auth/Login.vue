<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbInput from '@/Components/UI/TdbInput.vue';
import SketchCheckbox from '@/Components/Sketch/SketchCheckbox.vue';
const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') });
</script>
<template>
  <Head title="Log in" /><GuestLayout
    ><h1 class="tdb-heading text-3xl">Welcome back.</h1>
    <p class="mt-2 text-sm text-[var(--muted)]">Pick up where your work left off.</p>
    <form class="mt-7 space-y-5" @submit.prevent="submit">
      <TdbInput
        id="email"
        v-model="form.email"
        label="Email"
        type="email"
        required
        autocomplete="email"
        placeholder="you@example.com"
        :error="form.errors.email"
      /><TdbInput
        id="password"
        v-model="form.password"
        label="Password"
        type="password"
        required
        autocomplete="current-password"
        placeholder="••••••••"
        :error="form.errors.password"
      />
      <div class="flex items-center justify-between gap-3">
        <SketchCheckbox :checked="form.remember" label="Remember me" @change="form.remember = $event" /><Link
          :href="route('password.request')"
          class="text-sm font-semibold text-[var(--primary)] hover:underline"
          >Forgot password?</Link
        >
      </div>
      <TdbButton class="w-full" type="submit" :disabled="form.processing">{{
        form.processing ? 'Signing in…' : 'Log in'
      }}</TdbButton>
    </form>
    <p class="mt-6 text-center text-sm text-[var(--muted)]">
      New to TDB?
      <Link :href="route('register')" class="font-bold text-[var(--primary)] hover:underline">Create an account</Link>
    </p></GuestLayout
  >
</template>
