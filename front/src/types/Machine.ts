import type { Forum } from './Forum'
import type { InfoMachine } from './InfoMachine'

export interface Machine {
  id: number
  uuid?: string | null
  name: string | null
  dateCreation: string | null
  dateModif?: string | null
  visible: boolean | null
  forum?: Forum | null
  infoMachines?: InfoMachine[]
  image?: Blob | null
}
