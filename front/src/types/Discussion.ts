export interface DiscussionHistoryEntry {
  date: string // ISO string
  text: string
}

export interface Discussion {
  id: number
  postId: number | null
  titre: string
  contenu: string
  history: DiscussionHistoryEntry[]
}
