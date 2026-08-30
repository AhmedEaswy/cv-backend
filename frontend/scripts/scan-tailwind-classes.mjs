import fs from 'fs'
import path from 'path'

const root = path.resolve('app')
const dirs = ['components', 'pages', 'layouts']
const files = []

for (const d of dirs) {
  const walk = (p) => {
    for (const e of fs.readdirSync(p, { withFileTypes: true })) {
      const f = path.join(p, e.name)
      if (e.isDirectory()) walk(f)
      else if (e.name.endsWith('.vue')) files.push(f)
    }
  }
  walk(path.join(root, d))
}

const tailwind = []
const twRe = /\b(?:flex|grid|text-|bg-|rounded|shadow|max-w|min-h|px-|py-|gap-|items-|justify-|sm:|md:|lg:)/

for (const f of files) {
  const content = fs.readFileSync(f, 'utf8')
  const rel = path.relative(root, f).replace(/\\/g, '/')
  const hasScoped = /<style scoped>/.test(content)

  for (const [, cls] of content.matchAll(/class="([^"]+)"/g)) {
    const utilityCount = (cls.match(/\b[a-z0-9_-]+(?::[a-z0-9_-]+)?(?:\/\[[^\]]+\])?/gi) || []).length
    if (cls.length >= 60 && twRe.test(cls) && utilityCount >= 5) {
      tailwind.push({ file: rel, len: cls.length, utilities: utilityCount, hasScoped, sample: cls })
    }
  }
}

tailwind.sort((a, b) => b.len - a.len)
fs.writeFileSync('scripts/tailwind-long-classes.json', JSON.stringify(tailwind, null, 2), 'utf8')
console.log('Found', tailwind.length, 'long Tailwind class strings')
tailwind.forEach((t) => console.log(`${t.len} [${t.hasScoped ? 'scoped' : 'no-scoped'}] ${t.file}`))
