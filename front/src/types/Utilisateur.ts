import type { Droit } from './Droit'
import type { Message } from './Message'
import type { Forum } from './Forum'
import type { Moderations } from './Moderations'

export type UserStatus =
  | 'active'
  | 'inactive'
  | 'suspended'
  | 'pending'
  | 'banned'
  | 'deleted'


export interface Utilisateur {
  id: number
  username: string
  roles: string[]
  password?: string | null
  dateCreation: string | null
  anonimus: boolean | null
  lastVisit?: string | null
  mail?: string | null
  status: UserStatus
  updatedAt?: string | null
  deletedAt?: string | null
  droits?: Droit[]
  message?: Message | null
  forums?: Forum[]
  moderations?: Moderations | null
  cible?: Moderations | null
  createdAt?: string | null
}
