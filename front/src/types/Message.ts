import type { Utilisateur } from './Utilisateur'

export interface Message {
  id: number
  text: string | null
  dateCreation: string | null // ISO string for date
  dateModification?: string | null
  dateSuppresion?: string | null
  visible: boolean | null
  utilisateur: Utilisateur
}
