import type { Message } from './Message'

export interface Post {
  id: number
  titre: string
  dateCreation: string // ISO string
  vues: number | 0
  verrouille?: boolean | false
  epingle?: boolean | false
  messages?: Message[]
}
