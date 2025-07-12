<template>
  <v-list>
    <v-list-item v-for="forum in filteredForums" :key="'forum-'+forum.id">
      <v-list-item-title>{{ forum.titre }}</v-list-item-title>
      <v-list-item-subtitle>
        <div v-for="post in filteredPosts.filter(p => p.forumId === forum.id)" :key="'post-'+post.id" style="margin-left: 1.5em;">
          <strong>- {{ post.titre }}</strong>
          <v-btn size="x-small" color="error" @click="$emit('delete-post', post)">Supprimer post</v-btn>
          <v-select
            v-model="post.forumId"
            :items="forumSelectItems"
            label="Déplacer vers forum"
            dense hide-details style="max-width:180px;display:inline-block;margin-left:8px;"
          />
          <div v-for="discussion in filteredDiscussions.filter(d => d.postId === post.id)" :key="'discussion-'+discussion.id" style="margin-left: 2.5em;">
            <span @click="$emit('select-discussion', discussion)" style="cursor:pointer;">
              {{ discussion.titre }}
            </span>
            <v-btn size="x-small" color="error" @click="$emit('delete-discussion', discussion)">Supprimer discussion</v-btn>
            <v-select
              v-model="discussion.postId"
              :items="postSelectItems"
              label="Déplacer vers post"
              dense hide-details style="max-width:180px;display:inline-block;margin-left:8px;"
            />
          </div>
        </div>
      </v-list-item-subtitle>
    </v-list-item>
    <v-list-item v-if="orphanDiscussions.length">
      <v-list-item-title>Discussions sans post</v-list-item-title>
      <v-list-item-subtitle>
        <div v-for="discussion in orphanDiscussions" :key="'orphan-discussion-'+discussion.id" style="margin-left: 2.5em;">
          <span @click="$emit('select-discussion', discussion)" style="cursor:pointer;">
            {{ discussion.titre }}
          </span>
          <v-btn size="x-small" color="error" @click="$emit('delete-discussion', discussion)">Supprimer discussion</v-btn>
          <v-select
            v-model="discussion.postId"
            :items="postSelectItems"
            label="Déplacer vers post"
            dense hide-details style="max-width:180px;display:inline-block;margin-left:8px;"
          />
        </div>
      </v-list-item-subtitle>
    </v-list-item>
  </v-list>
</template>

<script setup lang="ts">
import type { Forum } from '@/types/Forum'
import type { Post } from '@/types/Post'
import type { Message } from '@/types/Message'

interface PostAdmin extends Post { forumId: number }
interface MessageAdmin extends Message { postId: number; titre: string; history?: { date: string; text: string }[] }

defineProps<{
  filteredForums: Forum[]
  filteredPosts: PostAdmin[]
  filteredDiscussions: MessageAdmin[]
  orphanDiscussions: MessageAdmin[]
  forumSelectItems: { title: string; value: number }[]
  postSelectItems: { title: string; value: number }[]
}>()

defineEmits(['delete-post', 'delete-discussion', 'select-discussion'])
</script>
