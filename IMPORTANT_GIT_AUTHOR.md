# ⚠️ IMPORTANT: Git Author Configuration

## Your Question Answered

**Q: If all of us push from one device/one Google account in GitHub, will it show that it was from one person but different branch names? Or the teacher wouldn't know the difference?**

**A: YES, the teacher WILL know it's from one person!** ❌

## The Problem

- ❌ **Branch names DON'T matter** - GitHub doesn't show who owns a branch
- ✅ **Commit author DOES matter** - GitHub shows who made each commit
- ❌ If everyone uses the same Git config, **ALL commits show as from the same person**
- ❌ Even on different branches, if the author is the same, it looks like one person did everything

## The Solution

**Each person MUST set their own Git identity before committing!**

### Quick Setup (Run This First!)

**Windows:**
```powershell
.\setup-git-identity.ps1
```

**Mac/Linux:**
```bash
bash setup-git-identity.sh
```

### Manual Setup

Each person, on their branch:

```bash
# Switch to your branch
git checkout ibrahim  # or daban, rasa, abdullah, ahmad

# Set YOUR name and email
git config user.name "Your Full Name"
git config user.email "your-github-email@example.com"

# Verify it worked
git config user.name
git config user.email
```

## What GitHub Shows

### ❌ WITHOUT Setting Git Config (Wrong):
```
All commits show:
- Author: IbrahimQaderBraim
- Author: IbrahimQaderBraim  
- Author: IbrahimQaderBraim
- Author: IbrahimQaderBraim
- Author: IbrahimQaderBraim
```
**Result:** Teacher sees ONE person did everything! 😱

### ✅ WITH Setting Git Config (Correct):
```
Commits show:
- Author: Ibrahim Qader Braim (on ibrahim branch)
- Author: Daban [Name] (on daban branch)
- Author: Rasa [Name] (on rasa branch)
- Author: Abdullah [Name] (on abdullah branch)
- Author: Ahmad [Name] (on ahmad branch)
```
**Result:** Teacher sees FIVE different people contributed! ✅

## How to Find Your GitHub Email

1. Go to GitHub.com
2. Click your profile → Settings
3. Go to "Emails"
4. Use your primary email OR the GitHub no-reply email:
   - Format: `username@users.noreply.github.com`

## Example Workflow

```bash
# 1. Switch to your branch
git checkout daban

# 2. Set YOUR identity (ONE TIME)
git config user.name "Daban [Your Full Name]"
git config user.email "daban@example.com"  # Your GitHub email

# 3. Verify
git config user.name
git config user.email

# 4. Now make commits (they'll show YOUR name!)
git add [files]
git commit -m "feat: My work"
git push origin daban
```

## Key Points

1. ✅ **Set Git config ONCE per branch** - It persists
2. ✅ **Use your GitHub email** - So commits link to your profile
3. ✅ **Branch names are just organization** - They don't affect authorship
4. ✅ **Each commit shows the author** - That's what GitHub displays

## What Your Teacher Will See

### On GitHub Repository:
- **Contributors tab:** Shows 5 different people (if configs are set correctly)
- **Commit history:** Each commit shows different author
- **Pull Requests:** Each PR shows who created it and their commits

### If You DON'T Set Config:
- **Contributors tab:** Shows only 1 person (Ibrahim)
- **Commit history:** All commits show same author
- **Teacher will know:** Only one person did the work! ❌

## Summary

**Answer:** The teacher WILL know if you don't set Git configs! Branch names don't hide the fact that all commits are from one person.

**Solution:** Each person must run `setup-git-identity.ps1` (or `.sh`) or manually set their Git config before making commits.

**Result:** Each commit will show the correct author, and the teacher will see 5 different contributors! ✅

---

**See `SETUP_GIT_CONFIG.md` for detailed instructions.**

