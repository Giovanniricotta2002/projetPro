import type { Post } from './Post'
import type { CategorieForum } from './CategorieForum'
import type { Utilisateur } from './Utilisateur'
import type { Machine } from './Machine'

export interface Forum {
  id: number
  titre: string | null
  dateCreation: string | null // ISO string
  dateCloture?: string | null
  description?: string | null
  ordreAffichage: number | null
  visible: boolean | null
  slug: string | null
  createdAt: string | null
  updatedAt: string | null
  deletedAt: string | null
  post?: Post[]
  categorieForums?: CategorieForum[]
  utilisateur?: Utilisateur | null
  machine?: Machine | null
}
